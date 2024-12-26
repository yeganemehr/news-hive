<?php

namespace App\Providers;

use App\Sources\Guardian;
use App\Sources\NYTimes;
use Illuminate\Contracts\Container\Container;
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
    }
}
