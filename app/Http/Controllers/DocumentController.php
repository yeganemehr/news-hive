<?php

namespace App\Http\Controllers;

use App\Enums\DocumentSource;
use App\Http\Requests\UpdateDocumentRequest;
use App\Http\Resources\DocumentCollection;
use App\Http\Resources\DocumentResource;
use App\Models\Author;
use App\Models\Document;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    public function index(Request $request): DocumentCollection
    {
        $validator = Validator::make($request->query(), [
            'tag-slug' => ['sometimes', 'required', 'string', Rule::exists(Tag::class, 'slug')],
            'author-slug' => ['sometimes', 'required', 'string', Rule::exists(Author::class, 'slug')],
            'published-from' => ['sometimes', 'required', 'date'],
            'published-to' => ['sometimes', 'required', 'date'],
            'source-type' => ['sometimes', 'required', Rule::in(array_column(DocumentSource::cases(), 'value'))],
        ]);
        $filters = $validator->validate();

        $query = Document::query()
            ->with(['authors', 'tags'])
            ->withoutContent()
            ->filter($filters)
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
