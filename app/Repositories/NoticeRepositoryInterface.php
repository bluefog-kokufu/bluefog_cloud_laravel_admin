<?php

namespace App\Repositories;

use App\Models\Notice;
use Illuminate\Pagination\LengthAwarePaginator;

interface NoticeRepositoryInterface
{
    /**
     * お知らせを公開日の新しい順に取得する
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * お知らせを1件取得する
     */
    public function find(int $id): ?Notice;

    /**
     * お知らせを新規作成する
     */
    public function create(array $data): Notice;

    /**
     * お知らせを更新する
     */
    public function update(Notice $notice, array $data): Notice;

    /**
     * お知らせを削除する
     */
    public function delete(Notice $notice): void;
}
