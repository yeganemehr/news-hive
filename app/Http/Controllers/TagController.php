<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HasDocuments;
use App\Http\Requests\StoreTagRequest;
use App\Http\Requests\UpdateTagRequest;
use App\Http\Resources\TagCollection;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\Response;

class TagController extends Controller
{
    use HasDocuments;

    public function index(): TagCollection
    {
        $query = Tag::query();
        $this->eagerLoadDocuments($query);

        return new TagCollection($query->cursorPaginate());
    }

    public function store(StoreTagRequest $request): TagResource
    {
        $tag = Tag::query()->create($request->validated());

        return new TagResource($tag);
    }

    public function show(Tag $tag): TagResource
    {
        $this->lazyLoadDocuments($tag);

        return new TagResource($tag);
    }

    public function update(UpdateTagRequest $request, Tag $tag): TagResource
    {
        $tag->update($request->validated());
        $this->lazyLoadDocuments($tag);

        return new TagResource($tag);
    }

    public function destroy(Tag $tag): Response
    {
        $tag->delete();

        return response()->noContent();
    }
}
