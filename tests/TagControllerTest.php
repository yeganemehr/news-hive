<?php

namespace Tests;

use App\Models\Tag;
use Illuminate\Http\Response;

class TagControllerTest extends TestCase
{
    public function test_index()
    {
        Tag::factory()->count(3)->create();

        $response = $this->getJson(route('tags.index'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'slug', 'documents_count', 'created_at', 'updated_at'],
                ],
            ]);
    }

    public function test_store()
    {
        $data = Tag::factory()->make()->toArray();

        $response = $this->postJson(route('tags.store'), $data);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(['data' => ['id', 'title', 'slug', 'created_at', 'updated_at']]);

        $this->assertDatabaseHas('tags', $data);
    }

    public function test_show()
    {
        $tag = Tag::factory()->create();

        $response = $this->getJson(route('tags.show', $tag));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['data' => ['id', 'title', 'slug', 'documents_count', 'created_at', 'updated_at']]);
    }

    public function test_update()
    {
        $tag = Tag::factory()->create();
        $data = Tag::factory()->make()->toArray();

        $response = $this->putJson(route('tags.update', $tag), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['data' => ['id', 'title', 'slug', 'documents_count', 'created_at', 'updated_at']]);

        $this->assertDatabaseHas('tags', $data);
    }

    public function test_destroy()
    {
        $tag = Tag::factory()->create();

        $response = $this->deleteJson(route('tags.destroy', $tag));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }
}
