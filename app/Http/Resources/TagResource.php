<?php

namespace App\Http\Resources;

use App\Models\DocumentTag;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Tag $resource
 */
class TagResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'slug' => $this->resource->slug,
            'title' => $this->resource->title,
            'role' => $this->whenPivotLoaded(new DocumentTag, fn () => $this->resource->pivot->role),
            'documents_count' => $this->whenCounted('documents'),
            'latest_documents' => new DocumentCollection($this->whenLoaded('documents')),
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
