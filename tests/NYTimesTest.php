<?php

namespace Tests;

use App\Enums\DocumentSource;
use App\Models\Author;
use App\Models\Document;
use App\Models\Tag;
use App\Sources\NYTimes;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Str;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

class NYTimesTest extends TestCase
{
    private NYTimes $nytimes;

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
        $this->nytimes = new NYTimes('test', $this->logger);
        $this->nytimes->setClient($this->client);
    }

    public function test_fetch_with_valid_response()
    {
        $article = [
            '_id' => '123',
            'headline' => [
                'main' => fake()->words(3, true),
            ],
            'web_url' => fake()->url,
            'pub_date' => fake()->dateTime->format(DateTime::ATOM),
            'multimedia' => [
                [
                    'type' => 'image',
                    'width' => 50,
                    'height' => 60,
                    'url' => 'smaller-image.png',
                ],
                [
                    'type' => 'image',
                    'width' => 100,
                    'height' => 200,
                    'url' => 'image-that-used.png',
                ],
            ],
            'byline' => [
                'person' => [
                    [
                        'firstname' => 'Author',
                        'middlename' => null,
                        'lastname' => '1',
                    ],
                    [
                        'firstname' => 'Author',
                        'middlename' => null,
                        'lastname' => '2',
                    ],
                ],
            ],
            'section_name' => 'Category 1',
            'subsection_name' => 'Category 2',
            'keywords' => [
                ['value' => 'Keyword 1'],
                ['value' => 'Keyword 2'],
            ],
        ];
        $response = new Response(200, [], json_encode([
            'status' => 'OK',
            'response' => [
                'docs' => [$article],
            ],
        ]));

        $this->logger->method('error')->willThrowException(new Exception('Should not called'));
        $this->client->method('get')->willReturn($response);

        $this->nytimes->fetch(200, true);

        $this->assertDatabaseHas(Document::class, [
            'source_type' => DocumentSource::NYTIMES,
            'source_id' => $article['_id'],
            'title' => $article['headline']['main'],
            'image' => 'https://static01.nyt.com/' . $article['multimedia'][1]['url'],
        ]);

        foreach (['Category 1', 'Category 2', 'Keyword 1', 'Keyword 2'] as $title) {
            $this->assertDatabaseHas(Tag::class, [
                'title' => $title,
                'slug' => Str::slug($title),
            ]);
        }
        foreach (['Author 1', 'Author 2'] as $name) {
            $this->assertDatabaseHas(Author::class, [
                'name' => $name,
                'slug' => Str::slug($name),
            ]);
        }
    }

    public function test_fetch_with_invalid_response()
    {
        $response = new Response(200, [], json_encode(['invalid' => 'response']));

        $this->client->method('get')->willReturn($response);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid response status from NYTimes API');

        $this->nytimes->fetch(200, true);
    }
}
