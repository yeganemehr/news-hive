<?php

namespace Database\Seeders;

use App\Sources\ESPN;
use Illuminate\Database\Seeder;

class ESPNSeeder extends Seeder
{
    public function __construct(protected ESPN $espn) {}

    public function run(): void
    {
        $this->espn->fetch(200, true);
    }
}
