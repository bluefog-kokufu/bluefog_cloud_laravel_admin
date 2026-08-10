# Project

社内管理画面用Laravelプロジェクト（ユーザー向けフロントは`bluefog_cloud_laravel`）

Laravel 13.23.0

Apache 2.4.62

PHP 8.4.24

MySQL 8.4.11

Docker Compose（`bluefog_cloud_laravel`と同じdb/webコンテナを共有）

Composer(PHP) 2.10.2

# アクセス

http://admin.localhost:8080

Apacheのネームベースバーチャルホストで`bluefog_cloud_laravel`(front.localhost)と分離している。
DocumentRootは`/var/www/admin/public`。

# データベース

DB名: laravel_admin（frontの`laravel`DBとは別。同じ`laravel`ユーザーで接続可能）

# Coding Rule

Controllerは薄くする

Business LogicはServiceへ

Repository Patternを使用

Request Validationを利用

Eloquentのみ利用

# Coding Style

PSR-12

コメントは日本語

変数名は英語

# Git

featureブランチで開発

mainへ直接Push禁止
