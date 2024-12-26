<?php

namespace App\Providers;

use App\Sources\Guardian;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Guardian::class, fn () => new Guardian(config('sources.guardian.api-key')));
    }
}
