<?php

namespace Database\Factories;

use App\Enums\DocumentSource;
use App\Models\Document;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition()
    {
        $title = $this->faker->unique()->sentence;

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'content' => $this->faker->paragraph,
            'image' => $this->faker->imageUrl(),
            'source_type' => $this->faker->randomElement([
                DocumentSource::ESPN,
                DocumentSource::GUARDIAN,
                DocumentSource::NYTIMES,
            ]),
            'source_id' => Str::uuid(),
            'published_at' => Carbon::createFromInterface($this->faker->dateTime),
        ];
    }
}
