<?php

namespace App\Enums;

use App\Contracts\ISource;
use App\Sources\ESPN;
use App\Sources\Guardian;
use App\Sources\NYTimes;

enum DocumentSource: string
{
    case GUARDIAN = 'guardian';
    case NYTIMES = 'nytimes';
    case ESPN = 'espn';

    public function getHandler(): ISource
    {
        $abstract = match ($this) {
            DocumentSource::GUARDIAN => Guardian::class,
            DocumentSource::NYTIMES => NYTimes::class,
            DocumentSource::ESPN => ESPN::class,
        };

        return app($abstract);
    }
}
