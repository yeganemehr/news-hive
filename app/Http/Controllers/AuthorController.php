<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HasDocuments;
use App\Http\Requests\StoreAuthorRequest;
use App\Http\Requests\UpdateAuthorRequest;
use App\Http\Resources\AuthorCollection;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use Illuminate\Http\Response;

class AuthorController extends Controller
{
    use HasDocuments;

    public function index(): AuthorCollection
    {
        $query = Author::query();
        $this->eagerLoadDocuments($query);

        return new AuthorCollection($query->cursorPaginate());
    }

    public function store(StoreAuthorRequest $request): AuthorResource
    {
        $author = Author::query()->create($request->validated());

        return new AuthorResource($author);
    }

    public function show(Author $author): AuthorResource
    {
        $this->lazyLoadDocuments($author);

        return new AuthorResource($author);
    }

    public function update(UpdateAuthorRequest $request, Author $author): AuthorResource
    {
        $author->update($request->validated());
        $this->lazyLoadDocuments($author);

        return new AuthorResource($author);
    }

    public function destroy(Author $author): Response
    {
        $author->delete();

        return response()->noContent();
    }
}
