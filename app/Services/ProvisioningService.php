<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use RuntimeException;
use Throwable;

/**
 * 契約企業(テナント)ごとにfront側Laravelプロジェクトを自動生成する。
 * git clone → .env生成 → DB作成 → 依存関係インストール → migrate --seed → 内部bootstrap API呼び出し、の順で実行する。
 * 実行にはgitのSSHデプロイ鍵がコンテナ内に必要(config('tenant.repository_url')参照)。
 */
class ProvisioningService
{
    /**
     * 出力ログを保存する際の最大文字数(provision_errorの肥大化を防ぐ)
     */
    private const ERROR_LOG_LIMIT = 3000;

    public function provision(Company $company): void
    {
        try {
            $path = $this->tenantPath($company);

            $this->cloneRepository($company, $path);
            $this->createDatabase($company);
            $tenantEnv = $this->writeEnvFile($company, $path);
            $this->installDependencies($path, $tenantEnv);
            $this->generateAppKey($path, $tenantEnv);
            $this->runMigrations($path, $tenantEnv);
            $this->callBootstrapApi($company);

            $company->update(['provision_status' => 'active', 'provision_error' => null]);
        } catch (Throwable $e) {
            $company->update([
                'provision_status' => 'failed',
                'provision_error' => mb_substr($e->getMessage(), 0, self::ERROR_LOG_LIMIT),
            ]);

            throw $e;
        }
    }

    /**
     * 失敗したプロビジョニングを再実行する前に、途中まで生成されたディレクトリ・DBを削除する
     */
    public function cleanupPartialState(Company $company): void
    {
        $path = $this->tenantPath($company);
        if (is_dir($path)) {
            File::deleteDirectory($path);
        }

        DB::statement("DROP DATABASE IF EXISTS `{$this->databaseName($company)}`");
    }

    private function tenantPath(Company $company): string
    {
        return rtrim(config('tenant.tenants_path'), '/').'/'.$company->slug;
    }

    private function databaseName(Company $company): string
    {
        return 'laravel_'.str_replace('-', '_', $company->slug);
    }

    /**
     * テナントの公開URL(APP_URL・メール内リンク等に使う)を組み立てる。
     * ローカル環境はTLS証明書が無くdocker-composeの公開ポートも必要なため、
     * TENANT_APACHE_SCHEME/TENANT_APACHE_PORTで上書きできるようにしている
     */
    private function tenantUrl(Company $company): string
    {
        $scheme = config('tenant.apache_scheme');
        $port = config('tenant.apache_port');
        $host = $company->slug.'.'.config('tenant.apache_domain');

        return "{$scheme}://{$host}".($port ? ":{$port}" : '');
    }

    private function cloneRepository(Company $company, string $path): void
    {
        if (is_dir($path)) {
            throw new RuntimeException("clone先が既に存在します: {$path}");
        }

        // マウント直後等でtenants親ディレクトリの存在確認に失敗する場合があるため、clone前に保証しておく
        $parentDir = dirname($path);
        if (! is_dir($parentDir) && ! mkdir($parentDir, 0755, true) && ! is_dir($parentDir)) {
            throw new RuntimeException("テナント用ディレクトリを作成できません: {$parentDir}");
        }

        $this->run(['git', 'clone', '--depth', '1', config('tenant.repository_url'), $path]);
    }

    private function createDatabase(Company $company): void
    {
        $database = $this->databaseName($company);

        // データベース名はslugのバリデーション(英数字・ハイフンのみ)を経由した値のみから生成されるためSQLインジェクションの余地はない
        DB::statement("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    /**
     * テナント用.envの内容を組み立てる。この配列はファイルへの書き込みだけでなく、
     * テナント配下で実行するコマンドのプロセス環境変数としても使う(下記writeEnvFileの注記を参照)
     */
    private function tenantEnv(Company $company): array
    {
        return [
            'APP_NAME' => $company->name,
            'APP_ENV' => 'production',
            // 空でも行として存在させておく必要がある。key:generate --forceは既存のAPP_KEY=行を
            // 正規表現で置換する実装のため、行自体が無いと生成した鍵を書き込めない
            'APP_KEY' => '',
            'APP_DEBUG' => 'false',
            'APP_URL' => $this->tenantUrl($company),
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => config('tenant.tenant_db.host'),
            'DB_PORT' => (string) config('tenant.tenant_db.port'),
            'DB_DATABASE' => $this->databaseName($company),
            'DB_USERNAME' => config('tenant.tenant_db.username'),
            'DB_PASSWORD' => config('tenant.tenant_db.password'),
            // front側のホーム画面「お知らせ」表示はadmin側laravel_admin DBのnoticesテーブルを
            // 直接参照する(App\Models\Noticeのconnection='admin_mysql')ため、この接続情報も必須
            'ADMIN_DB_HOST' => config('tenant.tenant_db.host'),
            'ADMIN_DB_PORT' => (string) config('tenant.tenant_db.port'),
            'ADMIN_DB_DATABASE' => 'laravel_admin',
            'ADMIN_DB_USERNAME' => config('tenant.tenant_db.username'),
            'ADMIN_DB_PASSWORD' => config('tenant.tenant_db.password'),
            'SESSION_DRIVER' => 'database',
            'CACHE_STORE' => 'database',
            'QUEUE_CONNECTION' => 'database',
            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => config('tenant.tenant_mail.host'),
            'MAIL_PORT' => (string) config('tenant.tenant_mail.port'),
            'MAIL_USERNAME' => config('tenant.tenant_mail.username'),
            'MAIL_PASSWORD' => config('tenant.tenant_mail.password'),
            'MAIL_FROM_ADDRESS' => config('tenant.tenant_mail.from_address'),
            'MAIL_FROM_NAME' => $company->name,
            'INTERNAL_BOOTSTRAP_SECRET' => $company->bootstrap_token,
        ];
    }

    /**
     * .envファイルを書き出す。artisanコマンドはこのプロセス(admin自身のqueue:workerで、
     * 既にadmin用DB_*等がputenv済み)の子プロセスとして実行されるため、.envファイルの値より
     * 継承した環境変数が優先されてしまう。そのためcomposer/artisan実行時は同じ値を
     * Process::env()で明示的に渡し、admin側の環境変数を上書きする(installDependencies等を参照)
     */
    private function writeEnvFile(Company $company, string $path): array
    {
        $env = $this->tenantEnv($company);

        $contents = collect($env)
            ->map(fn ($value, $key) => $key.'="'.addslashes((string) $value).'"')
            ->implode(PHP_EOL);

        file_put_contents($path.'/.env', $contents.PHP_EOL);

        return $env;
    }

    private function installDependencies(string $path, array $tenantEnv): void
    {
        $this->run(['composer', 'install', '--no-dev', '--optimize-autoloader'], $path, $tenantEnv, timeout: 600);
        $this->run(['npm', 'install'], $path, $tenantEnv, timeout: 600);
        $this->run(['npm', 'run', 'build'], $path, $tenantEnv, timeout: 600);
    }

    /**
     * vendor/autoload.phpがcomposer install後でないと存在せずartisanが動かないため、
     * 依存関係インストール後(migrateの前)に実行する
     */
    private function generateAppKey(string $path, array $tenantEnv): void
    {
        $this->run(['php', 'artisan', 'key:generate', '--force'], $path, $tenantEnv);
    }

    /**
     * 本番テナントにDatabaseSeeder(ローカル開発用のサンプル顧客・売上等)を投入すると
     * 契約企業に架空データが見えてしまうため、自社情報1行のみを作るProductionSeederを使う
     */
    private function runMigrations(string $path, array $tenantEnv): void
    {
        $this->run(['php', 'artisan', 'migrate', '--force'], $path, $tenantEnv);
        $this->run(['php', 'artisan', 'db:seed', '--class=ProductionSeeder', '--force'], $path, $tenantEnv);
    }

    /**
     * 公開DNS(ワイルドカード)やTLS証明書に依存させず、docker-compose内のwebサービスへ直接接続する。
     * Hostヘッダーでテナントのホスト名を指定することで、Apacheのワイルドカードvhost(VirtualDocumentRoot)に
     * 正しいテナントのDocumentRootを解決させる
     */
    private function callBootstrapApi(Company $company): void
    {
        // front側はメールリンク等をroute()ヘルパー(=リクエストのHostヘッダー)から生成するため、
        // ここにもtenantUrl()と同じポートを含めておかないとリンクからポートが欠落する
        $host = $company->slug.'.'.config('tenant.apache_domain');
        if ($port = config('tenant.apache_port')) {
            $host .= ":{$port}";
        }

        $response = Http::withToken($company->bootstrap_token)
            ->withHeaders(['Host' => $host])
            ->timeout(15)
            ->post(config('tenant.internal_web_url').'/internal/bootstrap', [
                'name' => $company->contact_name,
                'email' => $company->contact_email,
            ]);

        if ($response->failed()) {
            throw new RuntimeException("bootstrap API呼び出しに失敗しました(status: {$response->status()}): ".$response->body());
        }
    }

    private function run(array $command, ?string $path = null, array $env = [], int $timeout = 300): void
    {
        $process = Process::timeout($timeout);
        if ($path !== null) {
            $process = $process->path($path);
        }
        if ($env !== []) {
            $process = $process->env($env);
        }

        $result = $process->run($command);

        if ($result->failed()) {
            $commandLabel = implode(' ', $command);
            throw new RuntimeException("コマンド失敗: {$commandLabel}\n".$result->errorOutput().$result->output());
        }
    }
}
