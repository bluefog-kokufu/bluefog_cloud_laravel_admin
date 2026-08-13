<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\DB;
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

    private function tenantPath(Company $company): string
    {
        return rtrim(config('tenant.tenants_path'), '/').'/'.$company->slug;
    }

    private function databaseName(Company $company): string
    {
        return 'laravel_'.str_replace('-', '_', $company->slug);
    }

    private function tenantUrl(Company $company): string
    {
        return 'https://'.$company->slug.'.'.config('tenant.apache_domain');
    }

    private function cloneRepository(Company $company, string $path): void
    {
        if (is_dir($path)) {
            throw new RuntimeException("clone先が既に存在します: {$path}");
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
            'APP_DEBUG' => 'false',
            'APP_URL' => $this->tenantUrl($company),
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => config('tenant.tenant_db.host'),
            'DB_PORT' => (string) config('tenant.tenant_db.port'),
            'DB_DATABASE' => $this->databaseName($company),
            'DB_USERNAME' => config('tenant.tenant_db.username'),
            'DB_PASSWORD' => config('tenant.tenant_db.password'),
            'SESSION_DRIVER' => 'database',
            'CACHE_STORE' => 'database',
            'QUEUE_CONNECTION' => 'database',
            'MAIL_MAILER' => 'log',
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

    private function runMigrations(string $path, array $tenantEnv): void
    {
        $this->run(['php', 'artisan', 'migrate', '--seed', '--force'], $path, $tenantEnv);
    }

    /**
     * 公開DNS(ワイルドカード)やTLS証明書に依存させず、docker-compose内のwebサービスへ直接接続する。
     * Hostヘッダーでテナントのホスト名を指定することで、Apacheのワイルドカードvhost(VirtualDocumentRoot)に
     * 正しいテナントのDocumentRootを解決させる
     */
    private function callBootstrapApi(Company $company): void
    {
        $host = $company->slug.'.'.config('tenant.apache_domain');

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
