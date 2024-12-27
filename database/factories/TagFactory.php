<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition()
    {
        $title = $this->faker->unique()->words(2, true);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
        ];
    }
}
