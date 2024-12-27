<?php

namespace Tests;

use App\Enums\DocumentSource;
use App\Models\Author;
use App\Models\Document;
use App\Models\Tag;
use App\Sources\ESPN;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Str;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class ESPNTest extends TestCase
{
    private ESPN $espn;

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
        $this->espn = new ESPN($this->logger);
        $this->espn->setClient($this->client);
    }

    public function test_fetch_soccer_news_with_valid_response()
    {
        $firstResponse = new Response(200, [], json_encode([
            'articles' => [
                [
                    'dataSourceIdentifier' => '123',
                    'type' => 'Article',
                    'links' => [
                        'api' => [
                            'self' => [
                                'href' => 'https://example.com/article/123',
                            ],
                        ],
                    ],
                ],
            ],
        ]));

        $headline = [
            'dataSourceIdentifier' => '123',
            'type' => 'Article',
            'headline' => 'Test',
            'published' => fake()->dateTime->format(DateTime::ATOM),
            'images' => [
                [
                    'url' => fake()->imageUrl(),
                ],
            ],
            'story' => fake()->paragraph(),
            'byline' => fake()->name(),
            'categories' => [
                ['description' => 'category 1'],
                ['description' => 'category 2'],
            ],
            'keywords' => ['keyword 1', 'keyword 2'],
        ];
        $secondResponse = new Response(200, [], json_encode([
            'status' => 'success',
            'headlines' => [$headline],
        ]));

        $this->logger->method('error')->willThrowException(new Exception('Should not called'));
        $this->client->method('get')->willReturnOnConsecutiveCalls($firstResponse, $secondResponse);

        $this->espn->fetchSoccerNews('eng.1', 1);

        $this->assertDatabaseHas(Document::class, [
            'source_type' => DocumentSource::ESPN,
            'source_id' => $headline['dataSourceIdentifier'],
            'title' => $headline['headline'],
            'image' => $headline['images'][0]['url'],
            'content' => $headline['story'],
        ]);

        foreach (['category 1', 'category 2', 'keyword 1', 'keyword 2'] as $title) {
            $this->assertDatabaseHas(Tag::class, [
                'title' => $title,
                'slug' => Str::slug($title),
            ]);
        }

        $this->assertDatabaseHas(Author::class, [
            'name' => $headline['byline'],
            'slug' => Str::slug($headline['byline']),
        ]);
    }

    public function test_fetch_soccer_news_with_invalid_response()
    {
        $response = new Response(200, [], json_encode(['invalid' => 'response']));

        $this->client->method('get')->willReturn($response);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid response status from ESPN API');

        $this->espn->fetchSoccerNews('eng.1', 1);
    }
}
