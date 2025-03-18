<?php

namespace App\Providers;

// Контракты
use App\Contracts\Repositories\DocumentsRepositoryContract;
use App\Contracts\Repositories\FNSApiRepositoryContract;
use App\Contracts\Repositories\ListOfShareholdersRepositoryContract;

// Репозитории
use App\Repositories\DocumentsRepository;
use App\Repositories\FNSApiRepository;
use App\Repositories\ListOfShareholdersRepository;

use Illuminate\Support\ServiceProvider;


class RepositoriesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ListOfShareholdersRepositoryContract::class, ListOfShareholdersRepository::class);
        $this->app->singleton(DocumentsRepositoryContract::class, DocumentsRepository::class);
        $this->app->singleton(FNSApiRepositoryContract::class, FNSApiRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
