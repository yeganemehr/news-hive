<?php

namespace App\Enums;

use App\Sources\Guardian;

enum DocumentSource: string
{
    case GUARDIAN = 'guardian';

    public function getHandler()
    {
        return match ($this) {
            DocumentSource::GUARDIAN => app(Guardian::class),
        };
    }
}
