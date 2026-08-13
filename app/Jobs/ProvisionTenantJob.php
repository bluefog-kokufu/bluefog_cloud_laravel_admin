<?php

namespace App\Jobs;

use App\Models\Company;
use App\Services\ProvisioningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProvisionTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** クローン・composer install・migrateを含むため長めに確保する */
    public int $timeout = 1800;

    /** 途中で失敗したものを自動で再実行すると二重cloneなどが起きるため再試行しない */
    public int $tries = 1;

    public function __construct(private readonly int $companyId) {}

    public function handle(ProvisioningService $provisioningService): void
    {
        $company = Company::query()->findOrFail($this->companyId);

        $provisioningService->provision($company);
    }
}
