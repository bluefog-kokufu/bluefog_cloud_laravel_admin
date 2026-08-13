<?php

namespace App\Providers;

use App\Repositories\NoticeRepository;
use App\Repositories\NoticeRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(NoticeRepositoryInterface::class, NoticeRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
