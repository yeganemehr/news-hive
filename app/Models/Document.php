<?php

namespace App\Models;

use App\Enums\DocumentSource;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string $type
 * @property Carbon $published_at
 * @property string $image
 * @property string $content
 * @property string $slug
 * @property DocumentSource $source_type
 * @property string $source_id
 */
class Document extends Model
{
    use HasSlug;
    use HasUlids;

    protected $fillable = [
        'title',
        'published_at',
        'image',
        'content',
        'slug',
        'source_type',
        'source_id',
    ];

    protected $casts = [
        'source_type' => DocumentSource::class,
        'published_at' => 'datetime',
    ];

    protected array $columns = ['id', 'slug', 'title', 'image', 'content', 'source_type', 'source_id', 'created_at', 'updated_at', 'published_at'];

    public function scopeWithoutContent(Builder $query): void
    {
        $query->select(array_diff($this->columns, ['content']));
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'document_author');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withPivot('role')->using(DocumentTag::class);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function scopeByGuardian(Builder $query, ?string $id = null): void
    {
        $query->where('source_type', DocumentSource::GUARDIAN);
        if ($id != null) {
            $query->where('source_id', $id);
        }
    }

    public function scopeFilter(Builder $query, array $filters) {}

    public function scopeLatestSummerizedPublished($query, ?int $limit = 10): void
    {
        $query->orderByDesc('published_at');
        if ($limit !== null) {
            $query->limit($limit);
        }
        $query->withoutContent();
    }
}
