<?php

namespace App\Providers;

// Контракты
use App\Contracts\Services\DocumentsServiceContract;
use App\Contracts\Services\ListOfShareholdersServiceContract;

// Сервисы
use App\Services\DocumentsService;
use App\Services\ListOfShareholdersService;

use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DocumentsServiceContract::class, DocumentsService::class);
        $this->app->singleton(ListOfShareholdersServiceContract::class, ListOfShareholdersService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
