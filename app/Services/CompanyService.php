<?php

namespace App\Services;

use App\Jobs\ProvisionTenantJob;
use App\Models\Company;
use App\Repositories\CompanyRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use RuntimeException;

class CompanyService
{
    public function __construct(
        private readonly CompanyRepositoryInterface $companies,
        private readonly ProvisioningService $provisioningService,
    ) {}

    public function paginate(): LengthAwarePaginator
    {
        return $this->companies->paginate();
    }

    public function find(int $id): ?Company
    {
        return $this->companies->find($id);
    }

    /**
     * 契約企業を新規作成する。slugはfront側の自動生成(ディレクトリ名・DB名・サブドメイン)に使い回すため、
     * 作成時点ではプロビジョニング未実施として必ず「未着手」で登録する。
     * bootstrap_tokenはfront側の内部bootstrap APIを認証するテナント専用トークンとして自動発行する
     */
    public function create(array $data): Company
    {
        $data['provision_status'] = 'pending';
        $data['bootstrap_token'] = Str::random(40);

        return $this->companies->create($data);
    }

    /**
     * 契約企業を更新する。slugは作成後変更不可のため、送信されていても常に無視する
     */
    public function update(Company $company, array $data): Company
    {
        unset($data['slug']);

        return $this->companies->update($company, $data);
    }

    public function delete(Company $company): void
    {
        $this->companies->delete($company);
    }

    /**
     * front側環境の自動生成ジョブをキューへ投入する。未着手・失敗状態からのみ実行できる。
     * 失敗からの再実行の場合、途中まで生成された可能性のあるディレクトリ・DBを先に片付けてから
     * 最初からやり直す(cloneRepositoryはclone先が既に存在すると失敗するため)。
     * 実行中は「実行中(processing)」へ即座に変更してボタンを消し、二重実行(お互いのディレクトリ・DBを
     * 壊し合うレース)を防ぐ
     */
    public function startProvisioning(Company $company): void
    {
        if (! in_array($company->provision_status, ['pending', 'failed'], true)) {
            throw new RuntimeException('未着手または失敗状態のテナントのみプロビジョニングを開始できます。');
        }

        if ($company->provision_status === 'failed') {
            $this->provisioningService->cleanupPartialState($company);
        }

        $this->companies->update($company, ['provision_status' => 'processing', 'provision_error' => null]);

        ProvisionTenantJob::dispatch($company->id);
    }
}
