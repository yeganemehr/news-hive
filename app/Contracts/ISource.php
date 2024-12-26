<?php

namespace App\Contracts;

interface ISource
{
    public function fetch(int $maxItems, bool $seeding): void;
}
