<?php

namespace Database\Seeders;

use App\Sources\NYTimes;
use Illuminate\Database\Seeder;

class NYTimesSeeder extends Seeder
{
    public function __construct(protected NYTimes $times) {}

    public function run(): void
    {
        $this->times->fetch(200, true);
    }
}
