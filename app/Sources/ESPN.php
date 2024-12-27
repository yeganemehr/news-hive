<?php

namespace App\Sources;

use App\Contracts\ISource;
use App\Enums\DocumentSource;
use App\Enums\TagRole;
use App\Models\Author;
use App\Models\Document;
use App\Models\Tag;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

/**
 * These types are intentionally incomplete, for sake of simplicity.
 *
 * @phpstan-type Article array{dataSourceIdentifier:string,type:string}
 * @phpstan-type Image array{type:string,url:string}
 * @phpstan-type Category array{type:string,description?:string}
 * @phpstan-type Links array{web:array{href:string},api:array{self:array{href:string}}}
 * @phpstan-type ArticleHeadline array{dataSourceIdentifier:string,type:string,title?:string,headline:string,description:string,lastModified:string,published:string,images:Image[],categories:Category[],links:Links,byline?:string,story:string,keywords:string[]}
 */
class ESPN implements ISource
{
    private Client $client;

    public function __construct(private LoggerInterface $logger)
    {
        $this->client = new Client;
    }

    /**
     * @return $this
     */
    public function setClient(Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function fetch(int $maxItems, bool $seeding): void
    {
        $latest = Document::query()->orderByDesc('published_at')->byESPN()->first();
        if (!$latest and !$seeding) {
            throw new Exception('Cannot find latest document by ESPN in database. You may want to seed the database first');
        }

        $leagues = [
            'ger.1', // German Bundesliga
            'eng.1', // English Premier League
            'usa.1', // MLS
            'uefa.champions', // UEFA Champions League
            'esp.1', // Spanish LALIGA
            'ita.1', // Italian Serie A
        ];

        foreach ($leagues as $league) {
            $this->fetchSoccerNews($league, intval($maxItems / count($leagues)));
        }
    }

    public function fetchSoccerNews(string $league, int $maxItems): void
    {
        $response = $this->client->get("https://site.api.espn.com/apis/site/v2/sports/soccer/{$league}/news", [
            'query' => [
                'limit' => 50,
            ],
        ]);

        // I know using stdClass is more efficient than associative array but I can write PHPDoc for arrays not for stdClass
        // Based on items count, readability is more important than memory usage here.
        $data = json_decode($response->getBody(), true);
        if (!isset($data['articles'])) {
            throw new Exception('Invalid response status from ESPN API');
        }

        $itemSaved = 0;

        /**
         * We could use DTOs to parse & validate scheme of the response, but based on the context of this project, seems unnecessary.
         * So I KISS-ed it (!) and just cast the datatype for static analysis and more readability!
         *
         * @var array{articles:Article[]} $data
         */
        foreach ($data['articles'] as $item) {

            if ($item['type'] == 'Media') {
                // We dont' want media documents, they don't have content body.
                continue;
            }

            if ($item['type'] == 'Preview') {
                // We dont' want media documents, they don't have content body & image.
                continue;
            }

            try {
                if (Document::query()->byESPN($item['dataSourceIdentifier'])->exists()) {
                    break;
                }

                $this->fetchArticle($item['dataSourceIdentifier'], $item['links']['api']['self']['href']);
                $itemSaved++;
            } catch (Exception $e) {
                $this->logger->error('Error in saving a ESPN document: ' . $e->__toString(), [
                    'document' => [
                        'dataSourceIdentifier' => $item['dataSourceIdentifier'],
                    ],
                ]);
            }

            if ($itemSaved >= $maxItems) {
                break;
            }
        }
    }

    public function fetchArticle(string $dataSourceIdentifier, string $url): void
    {
        $response = $this->client->get($url);
        $data = json_decode($response->getBody(), true);
        if (!isset($data['status']) or $data['status'] != 'success') {
            throw new Exception('Invalid response status from ESPN API');
        }

        /**
         * @var array{status:'success',headlines:ArticleHeadline[]} $data
         * @var ArticleHeadline $headline
         */
        $headline = Arr::first($data['headlines'], fn ($item) => $item['dataSourceIdentifier'] == $dataSourceIdentifier);

        if (!$headline) {
            throw new Exception("Cannot find headline '{$dataSourceIdentifier}'");
        }

        $this->createDocument($headline);
    }

    /**
     * @param  ArticleHeadline  $item
     */
    private function createDocument(array $item): Document
    {
        return DB::transaction(function () use ($item) {
            $image = Arr::first($item['images']);
            if (!$image) {
                throw new Exception("Cannot find an image for {$item['dataSourceIdentifier']}");
            }

            $document = Document::query()->create([
                'source_type' => DocumentSource::ESPN,
                'source_id' => $item['dataSourceIdentifier'],
                'title' => $item['title'] ?? $item['headline'],
                'published_at' => Carbon::parse($item['published']),
                'image' => $image['url'],
                'content' => $item['story'],
            ]);

            if (isset($item['byline'])) {
                $authors = $this->getOrCreateAuthors($item['byline']);
                $document->authors()->sync($authors);
            }

            $tags = $this->getOrCreateTags($item['categories'], $item['keywords']);
            $document->tags()->sync($tags);

            return $document;
        });
    }

    /**
     * @return int[]
     */
    private function getOrCreateAuthors(string $byline): array
    {
        $persons = explode(',', $byline);
        $ids = [];
        foreach ($persons as $name) {
            $slug = Str::slug($name);
            $author = Author::query()->firstOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );
            $ids[] = $author->id;
        }

        return $ids;
    }

    /**
     * @param  Category[]  $categories
     * @param  string[]  $keywords
     * @return array<int,array{role:TagRole}>
     */
    private function getOrCreateTags(array $categories, array $keywords): array
    {
        $ids = [];

        $getOrCreate = function (string $title, TagRole $role) use (&$ids) {
            $tag = Tag::query()->firstOrCreate(
                ['slug' => Str::slug($title)],
                ['title' => $title]
            );
            $ids[$tag->id] = [
                'role' => $role,
            ];
        };

        foreach ($categories as $category) {
            if (isset($category['description'])) {
                $getOrCreate($category['description'], TagRole::CATEGORY);
            }
        }

        foreach ($keywords as $keyword) {
            $getOrCreate($keyword, TagRole::KEYWORD);
        }

        return $ids;
    }
}
