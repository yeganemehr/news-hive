<?php

namespace Tests;

use App\Models\Document;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class DocumentControllerTest extends TestCase
{
    public function test_index()
    {
        Document::factory()->count(3)->create();

        $response = $this->getJson(route('documents.index'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'slug', 'title', 'source' => ['name', 'reference'], 'created_at', 'updated_at', 'published_at'],
                ],
            ]);
    }

    public function test_show()
    {
        $document = Document::factory()->create();

        $response = $this->getJson(route('documents.show', $document));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'slug',
                    'title',
                    'content',
                    'source' => ['name', 'reference'],
                    'created_at',
                    'updated_at',
                    'published_at',
                    'tags',
                    'authors',
                ],
            ]);
    }

    public function test_update()
    {
        $document = Document::factory()->create();
        $data = Arr::except(Document::factory()->make()->toArray(), ['source_type', 'source_id']);

        $response = $this->putJson(route('documents.update', $document), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'slug',
                    'title',
                    'content',
                    'source' => ['name', 'reference'],
                    'created_at',
                    'updated_at',
                    'published_at',
                    'tags',
                    'authors',
                ],
            ]);

        $this->assertDatabaseHas('documents', [
            'id' => $document->id,
            ...Arr::except($data, ['published_at']),
        ]);
    }

    public function test_destroy()
    {
        $document = Document::factory()->create();

        $response = $this->deleteJson(route('documents.destroy', $document));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    }
}
