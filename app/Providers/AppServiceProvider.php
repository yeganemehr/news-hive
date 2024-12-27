<?php

namespace App\Providers;

use App\Sources\ESPN;
use App\Sources\Guardian;
use App\Sources\NYTimes;
use Dedoc\Scramble\Scramble;
use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Guardian::class, fn (Container $app) => new Guardian(
            apiKey: config('sources.guardian.api-key') ?? '',
            logger: $app->make('log'),
        ));

        $this->app->bind(NYTimes::class, fn (Container $app) => new NYTimes(
            apiKey: config('sources.nytimes.api-key') ?? '',
            logger: $app->make('log'),
        ));

        $this->app->bind(ESPN::class, fn (Container $app) => new ESPN(
            logger: $app->make('log'),
        ));

        Scramble::ignoreDefaultRoutes();
    }

    public function boot(): void
    {
        Gate::define('viewApiDocs', fn (?User $user) => true);

        Scramble::registerApi('v1', [
            'api_path' => 'api/v1',
        ]);

    }
}
