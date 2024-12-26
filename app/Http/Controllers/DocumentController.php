<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDocumentRequest;
use App\Http\Resources\DocumentCollection;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Http\Response;

class DocumentController extends Controller
{
    public function index(): DocumentCollection
    {
        $query = Document::query()
            ->with(['authors', 'tags'])
            ->withoutContent()
            ->orderByDesc('published_at');

        return new DocumentCollection($query->simplePaginate());
    }

    public function show(Document $document): DocumentResource
    {
        $document->load(['authors', 'tags']);

        return new DocumentResource($document);
    }

    public function update(UpdateDocumentRequest $request, Document $document): DocumentResource
    {
        $document->update($request->validated());
        $document->load(['authors', 'tags']);

        return new DocumentResource($document);
    }

    public function destroy(Document $document): Response
    {
        $document->delete();

        return response()->noContent();
    }
}
