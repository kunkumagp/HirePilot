<?php

namespace App\Providers;

use App\Contracts\ProfileRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\ProfileRepository;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ProfileRepositoryInterface::class, ProfileRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
