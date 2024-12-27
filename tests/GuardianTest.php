<?php

namespace Tests;

use App\Enums\DocumentSource;
use App\Models\Author;
use App\Models\Document;
use App\Models\Tag;
use App\Sources\Guardian;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Str;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class GuardianTest extends TestCase
{
    private Guardian $guardian;

    /**
     * @var MockObject&Client
     */
    private Client $client;

    /**
     * @var MockObject&LoggerInterface
     */
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(Client::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->guardian = new Guardian('test', $this->logger);
        $this->guardian->setClient($this->client);
    }

    public function test_fetch_with_valid_response()
    {
        $article = [
            'id' => '123',
            'webTitle' => fake()->words(3, true),
            'webPublicationDate' => fake()->dateTime->format(DateTime::ATOM),
            'fields' => [
                'thumbnail' => fake()->imageUrl(),
                'body' => fake()->paragraph(),
            ],
            'tags' => [
                ['type' => 'keyword', 'id' => 'keyword/category-1', 'webTitle' => 'Category 1'],
                ['type' => 'keyword', 'id' => 'keyword/keyword-1', 'webTitle' => 'Keyword 1'],
                ['type' => 'keyword', 'id' => 'keyword/keyword-2', 'webTitle' => 'Keyword 2'],
                ['type' => 'contributor', 'id' => 'contributor/author-1', 'webTitle' => 'Author 1'],
            ],
        ];
        $response = new Response(200, [], json_encode([
            'response' => [
                'status' => 'ok',
                'results' => [$article],
            ],
        ]));

        $this->logger->method('error')->willThrowException(new Exception('Should not called'));
        $this->client->method('get')->willReturn($response);

        $this->guardian->fetch(200, true);

        $this->assertDatabaseHas(Document::class, [
            'source_type' => DocumentSource::GUARDIAN,
            'source_id' => $article['id'],
            'title' => $article['webTitle'],
            'image' => $article['fields']['thumbnail'],
            'content' => $article['fields']['body'],
        ]);

        foreach (['Category 1', 'Keyword 1', 'Keyword 2'] as $title) {
            $this->assertDatabaseHas(Tag::class, [
                'title' => $title,
                'slug' => Str::slug($title),
            ]);
        }

        $this->assertDatabaseHas(Author::class, [
            'name' => 'Author 1',
            'slug' => Str::slug('Author 1'),
        ]);
    }

    public function test_fetch_with_invalid_response()
    {
        $response = new Response(200, [], json_encode(['invalid' => 'response']));

        $this->client->method('get')->willReturn($response);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid response status from Guardian API');

        $this->guardian->fetch(200, true);
    }
}
