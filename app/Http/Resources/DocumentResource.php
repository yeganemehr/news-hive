<?php

namespace App\Http\Resources;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Document $resource
 */
class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'slug' => $this->resource->slug,
            'title' => $this->resource->title,
            'content' => $this->whenHas('content'),
            'source' => [
                'name' => $this->resource->source_type,
                'reference' => $this->resource->source_id,
            ],
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'published_at' => $this->resource->published_at,
            'tags' => new TagCollection($this->whenLoaded('tags')),
            'authors' => new AuthorCollection($this->whenLoaded('authors')),
        ];
    }
}
