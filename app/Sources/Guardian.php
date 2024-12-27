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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

/**
 * These types are intentionally incomplete, for sake of simplicity.
 *
 * @phpstan-type SearchItemFields array{thumbnail:string,body:string,headline:string,lastModified:string}
 * @phpstan-type SearchItemKeywordTag array{type:'keyword',id:string,webTitle:string}
 * @phpstan-type SearchItemContributorTag array{type:'contributor',id:string,webTitle:string}
 * @phpstan-type SearchItemTag SearchItemKeywordTag|SearchItemContributorTag
 * @phpstan-type SearchItem array{id:string,webTitle:string,webPublicationDate:string,fields:SearchItemFields,tags:SearchItemTag[]}
 */
class Guardian implements ISource
{
    private Client $client;

    public function __construct(private string $apiKey, private LoggerInterface $logger)
    {
        $this->client = new Client([
            'base_uri' => 'https://content.guardianapis.com',
        ]);
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
        $latest = Document::query()->orderByDesc('published_at')->byGuardian()->first();
        if (!$latest and !$seeding) {
            throw new Exception('Cannot find latest document by guardian in database. You may want to seed the database first');
        }

        $itemsSaved = 0;
        $cursor = null;
        do {
            $url = $cursor ? "/content/{$cursor}/next" : '/search';
            $response = $this->client->get($url, [
                'query' => [
                    'order-by' => 'newest',
                    'page-size' => min($maxItems - $itemsSaved, 200),
                    'api-key' => $this->apiKey,
                    'show-fields' => implode(',', ['headline', 'body', 'lastModified', 'thumbnail', 'byline']),
                    'show-tags' => implode(',', ['keyword', 'contributor']),
                    'tag' => 'tone/news', // According to project's context, There is no technical hard limit.
                ],
            ]);

            // I know using stdClass is more efficient than associative array but I can write PHPDoc for arrays not for stdClass
            // Based on items count, readability is more important than memory usage here.
            $data = json_decode($response->getBody(), true);
            if (!isset($data['response']['status']) or $data['response']['status'] != 'ok') {
                throw new Exception('Invalid response status from Guardian API');
            }

            /**
             * We could use DTOs to parse & validate scheme of the response, but based on the context of this project, seems unnecessary.
             * So I KISS-ed it (!) and just cast the datatype for static analysis and more readability!
             *
             * @var array{response:array{status:'ok',results:SearchItem[]}} $data
             */
            foreach ($data['response']['results'] as $item) {
                if (($latest and $item['id'] == $latest->source_id) or $itemsSaved >= $maxItems) {
                    // It's a descending list
                    // Let's stop
                    break 2;
                }

                try {
                    $this->createDocument($item);
                    $itemsSaved++;
                } catch (Exception $e) {
                    $this->logger->error('Error in saving a Guardian document: ' . $e->__toString(), [
                        'document' => [
                            'id' => $item['id'],
                        ],
                    ]);
                }
                $cursor = $item['id'];
            }

            if (count($data['response']['results']) < 200) {
                break;
            }
        } while ($itemsSaved < $maxItems);
    }

    /**
     * @param  SearchItemTag[]  $tags
     * @return int[]
     */
    private function getOrCreateAuthors(array $tags): array
    {
        $ids = [];
        foreach ($tags as $tag) {
            if ($tag['type'] != 'contributor') {
                continue;
            }

            $slug = Str::after($tag['id'], '/');
            $author = Author::query()->firstOrCreate(
                ['slug' => $slug],
                ['name' => $tag['webTitle']]
            );
            $ids[] = $author->id;
        }

        return $ids;
    }

    /**
     * @param  SearchItemTag[]  $tags
     * @return array<int,array{role:TagRole}>
     */
    private function getOrCreateTags(array $tags): array
    {
        $first = true;
        $ids = [];
        foreach ($tags as $tag) {
            if ($tag['type'] != 'keyword') {
                continue;
            }

            $slug = Str::after($tag['id'], '/');
            $tag = Tag::query()->firstOrCreate(
                ['slug' => $slug],
                ['title' => $tag['webTitle']]
            );
            $ids[$tag->id] = [
                'role' => $first ? TagRole::CATEGORY : TagRole::KEYWORD,
            ];

            $first = false;
        }

        return $ids;
    }

    /**
     * @param  SearchItem  $item
     */
    private function createDocument(array $item): Document
    {
        return DB::transaction(function () use ($item) {
            $document = Document::query()->create([
                'source_type' => DocumentSource::GUARDIAN,
                'source_id' => $item['id'],
                'title' => $item['webTitle'],
                'published_at' => Carbon::parse($item['webPublicationDate']),
                'image' => $item['fields']['thumbnail'],
                'content' => $item['fields']['body'],
            ]);

            $authors = $this->getOrCreateAuthors($item['tags']);
            $document->authors()->sync($authors);

            $tags = $this->getOrCreateTags($item['tags']);
            $document->tags()->sync($tags);

            return $document;
        });
    }
}
