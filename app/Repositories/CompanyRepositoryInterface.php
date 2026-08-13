<?php

namespace App\Repositories;

use App\Models\Company;
use Illuminate\Pagination\LengthAwarePaginator;

interface CompanyRepositoryInterface
{
    /**
     * 契約企業を新しい順に取得する
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * 契約企業を1件取得する
     */
    public function find(int $id): ?Company;

    /**
     * 契約企業を新規作成する
     */
    public function create(array $data): Company;

    /**
     * 契約企業を更新する
     */
    public function update(Company $company, array $data): Company;

    /**
     * 契約企業を削除する
     */
    public function delete(Company $company): void;
}
