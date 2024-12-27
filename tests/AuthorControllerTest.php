<?php

namespace Tests;

use App\Models\Author;
use Illuminate\Http\Response;

class AuthorControllerTest extends TestCase
{
    public function test_index()
    {
        Author::factory()->count(3)->create();

        $response = $this->getJson(route('authors.index'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'documents_count', 'created_at', 'updated_at'],
                ],
            ]);
    }

    public function test_store()
    {
        $data = Author::factory()->make()->toArray();

        $response = $this->postJson(route('authors.store'), $data);

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(['data' => ['id', 'name', 'slug', 'created_at', 'updated_at']]);

        $this->assertDatabaseHas('authors', $data);
    }

    public function test_show()
    {
        $author = Author::factory()->create();

        $response = $this->getJson(route('authors.show', $author));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['data' => ['id', 'name', 'slug', 'documents_count', 'created_at', 'updated_at']]);
    }

    public function test_update()
    {
        $author = Author::factory()->create();
        $data = Author::factory()->make()->toArray();

        $response = $this->putJson(route('authors.update', $author), $data);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['data' => ['id', 'name', 'slug', 'documents_count', 'created_at', 'updated_at']]);

        $this->assertDatabaseHas('authors', $data);
    }

    public function test_destroy()
    {
        $author = Author::factory()->create();

        $response = $this->deleteJson(route('authors.destroy', $author));

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('authors', ['id' => $author->id]);
    }
}
