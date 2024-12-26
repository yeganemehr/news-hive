<?php

namespace Database\Seeders;

use App\Sources\Guardian;
use Illuminate\Database\Seeder;

class GuardianSeeder extends Seeder
{
    public function __construct(protected Guardian $guardian) {}

    public function run(): void
    {
        $this->guardian->fetch(200, true);
    }
}
