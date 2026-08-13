<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 予約済みslug
    |--------------------------------------------------------------------------
    |
    | slugはfront側の自動生成時にディレクトリ名・DB名・サブドメインへそのまま使い回すため、
    | 既存インフラや将来の用途と衝突しうる文字列はここでブロックする。
    |
    */

    'reserved_slugs' => [
        'www', 'admin', 'api', 'mail', 'ftp', 'smtp',
        'app', 'front', 'staging', 'dev', 'test', 'support', 'blog', 'localhost',
    ],

    /*
    |--------------------------------------------------------------------------
    | プロビジョニング設定
    |--------------------------------------------------------------------------
    |
    | 新規テナント(front側Laravelプロジェクト)の自動生成に使う設定。
    | webサービスとworkerサービスの双方から同じ値を参照する。
    |
    */

    // clone元のgitリポジトリ(SSHデプロイ鍵が必要)
    'repository_url' => env('TENANT_REPOSITORY_URL', 'git@github.com:bluefog-kokufu/bluefog_cloud_laravel.git'),

    // テナントごとのclone先の親ディレクトリ(docker-compose.ymlで./tenants:/var/www/tenantsをマウント)
    'tenants_path' => env('TENANT_PATH', '/var/www/tenants'),

    // ワイルドカードvhost/DNSで解決するベースドメイン({slug}.{apache_domain})。公開URLの生成(APP_URL等)に使う
    'apache_domain' => env('TENANT_APACHE_DOMAIN', 'bluefog-cloud.test'),

    // 内部bootstrap API呼び出し用。公開DNS/TLSに依存せずdocker-compose内のwebサービスへ直接接続する
    // (Hostヘッダーでテナントを指定し、Apacheのワイルドカードvhostに解決させる)
    'internal_web_url' => env('TENANT_INTERNAL_WEB_URL', 'http://web'),

    // テナントDBの接続先(同一MySQLサーバ上に laravel_{slug} を作成する)
    'tenant_db' => [
        'host' => env('TENANT_DB_HOST', env('DB_HOST', '127.0.0.1')),
        'port' => env('TENANT_DB_PORT', env('DB_PORT', '3306')),
        'username' => env('TENANT_DB_USERNAME', env('DB_USERNAME', 'root')),
        'password' => env('TENANT_DB_PASSWORD', env('DB_PASSWORD', '')),
    ],

];
