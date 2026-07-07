<?php

namespace App\Providers;

use App\Contracts\ProfileRepositoryInterface;
use App\Contracts\ResumeRepositoryInterface;
use App\Contracts\ResumeVersionRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\ProfileRepository;
use App\Repositories\Eloquent\ResumeRepository;
use App\Repositories\Eloquent\ResumeVersionRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Services\AI\AIProviderInterface;
use App\Services\AI\AIProviderManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ProfileRepositoryInterface::class, ProfileRepository::class);
        $this->app->bind(ResumeRepositoryInterface::class, ResumeRepository::class);
        $this->app->bind(ResumeVersionRepositoryInterface::class, ResumeVersionRepository::class);

        $this->app->singleton(AIProviderManager::class, function ($app) {
            return new AIProviderManager(config('ai.default', 'openai'));
        });

        $this->app->bind(AIProviderInterface::class, function ($app) {
            return $app->make(AIProviderManager::class);
        });
    }

    public function boot(): void
    {
        //
    }
}
