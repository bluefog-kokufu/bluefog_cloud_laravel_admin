<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            // front側の初回ユーザー(プロフィール)としてbootstrap APIへ送る氏名・メールアドレス
            $table->string('contact_name')->nullable()->after('slug');
            $table->string('contact_email')->nullable()->after('contact_name');
            // front側の.envへ書き込み、bootstrap API呼び出し時の認証に使うテナント専用トークン
            $table->string('bootstrap_token')->nullable()->after('provision_status');
            // プロビジョニング失敗時の直近のエラー内容
            $table->text('provision_error')->nullable()->after('bootstrap_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['contact_name', 'contact_email', 'bootstrap_token', 'provision_error']);
        });
    }
};
