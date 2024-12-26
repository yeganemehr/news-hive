<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Author;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;

trait HasDocuments
{
    protected function lazyLoadDocuments(Tag|Author $model): void
    {
        $model->loadCount('documents');
        $model->load([
            'documents' => fn ($query) => $query->latestSummerizedPublished(),
        ]);
    }

    protected function eagerLoadDocuments(Builder $query): Builder
    {
        $query->withCount('documents');
        $query->with('documents', fn ($query) => $query->latestSummerizedPublished());

        return $query;
    }
}
