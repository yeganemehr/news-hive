<?php

namespace App\Enums;

use App\Contracts\ISource;
use App\Sources\Guardian;
use App\Sources\NYTimes;

enum DocumentSource: string
{
    case GUARDIAN = 'guardian';
    case NYTIMES = 'nytimes';

    public function getHandler(): ISource
    {
        $abstract = match ($this) {
            DocumentSource::GUARDIAN => Guardian::class,
            DocumentSource::NYTIMES => NYTimes::class,
        };

        return app($abstract);
    }
}
