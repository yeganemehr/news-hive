<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property string $title
 * @property string $slug
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Tag extends Model
{
    use HasSlug;
    use HasUlids;

    protected $fillable = [
        'title',
        'slug',
    ];

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class)
            ->withPivot('role')
            ->using(DocumentTag::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }
}
