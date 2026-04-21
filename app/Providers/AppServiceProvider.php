<?php

namespace App\Providers;

use App\Repositories\Scammer\FrontendScammerRepository;
use Illuminate\Support\ServiceProvider;

use App\Http\Controllers\Public;
use App\Http\Controllers\Admin;

use App\Repositories\Organization\OrganizationRepositoryInterface;
use App\Repositories\Scammer\ScammerRepositoryInterface;

use App\Repositories\Organization\PublicOrganizationRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->when(Public\OrganizationController::class)
        ->needs(OrganizationRepositoryInterface::class)
        ->give(PublicOrganizationRepository::class);

        $this->app->when(Public\ScammerController::class)
        ->needs(ScammerRepositoryInterface::class)
        ->give(FrontendScammerRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
