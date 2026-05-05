<?php

namespace App\Providers;

use App\Infrastructure\Facebook\FacebookServiceInterface;
use App\Infrastructure\Facebook\FacebookService;

use App\Infrastructure\Instagram\InstagramService;
use App\Infrastructure\Instagram\InstagramServiceInterface;
use App\Infrastructure\TikTok\TikTokService;
use App\Infrastructure\TikTok\TikTokServiceInterface;
use App\Infrastructure\Youtube\YoutubeService;
use App\Infrastructure\Youtube\YoutubeServiceInterface;
use App\Repositories\Scammer\FrontendScammerRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

use App\Http\Controllers\Public;

use App\Repositories\Organization\OrganizationRepositoryInterface;
use App\Repositories\Scammer\ScammerRepositoryInterface;

use App\Repositories\Organization\FrontendOrganizationRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Model::preventLazyLoading(true);

        $this->app->singleton(FacebookServiceInterface::class, FacebookService::class);
        $this->app->singleton(YoutubeServiceInterface::class, YoutubeService::class);
        $this->app->singleton(InstagramServiceInterface::class, InstagramService::class);
        $this->app->singleton(TikTokServiceInterface::class, TikTokService::class);

        $this->app->when(Public\OrganizationController::class)
        ->needs(OrganizationRepositoryInterface::class)
        ->give(FrontendOrganizationRepository::class);

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
