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

// As I explained in README.md, NYTimes doesn't provide content of its articales in API but accroding to context of this project, it doesn't matter!
// You want me to code clean, strucutred and minimal, so I did my best!

/**
 * These types are intentionally incomplete, for sake of simplicity.
 *
 * @phpstan-type Article array{_id:string,web_url:string,headline:Headline,pub_date:string,document_type:string,type_of_material:string,keywords:Keyword[],multimedia:Multimedia[],section_name:string,subsection_name?:string,byline:Byline}
 * @phpstan-type Headline array{main:string}
 * @phpstan-type Person array{firstname:string,middlename:string,lastname:string}
 * @phpstan-type Byline array{person:Person[]}
 * @phpstan-type Keyword array{name:string,value:string,rank:int}
 * @phpstan-type Multimedia array{type:string,subtype:string,url:string,height:int,width:int}
 */
class NYTimes implements ISource
{
    private Client $client;

    public function __construct(private string $apiKey, private LoggerInterface $logger)
    {
        $this->client = new Client([
            'base_uri' => 'https://api.nytimes.com/svc/',
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
        if ($maxItems > 1000) {
            throw new Exception('There is hard limit for maxTimes in NYTimes which can be over 1000 items');
        }

        $latest = Document::query()->orderByDesc('published_at')->byNYTimes()->first();
        if (!$latest and !$seeding) {
            throw new Exception('Cannot find latest document by NYTimes in database. You may want to seed the database first');
        }

        $itemsSaved = 0;
        $page = 1;
        do {
            $response = $this->client->get('search/v2/articlesearch.json', [
                'query' => [
                    'api-key' => $this->apiKey,
                    'page' => $page,
                    'sort' => 'newest',
                ],
            ]);
            $page++;

            // I know using stdClass is more efficient than associative array but I can write PHPDoc for arrays not for stdClass
            // Based on items count, readability is more important than memory usage here.
            $data = json_decode($response->getBody(), true);
            if (!isset($data['status']) or $data['status'] != 'OK') {
                throw new Exception('Invalid response status from NYTimes API');
            }

            /**
             * We could use DTOs to parse & validate scheme of the response, but based on the context of this project, seems unnecessary.
             * So I KISS-ed it (!) and just cast the datatype for static analysis and more readability!
             *
             * @var array{status:'OK',response:array{docs:Article[]}} $data
             */
            foreach ($data['response']['docs'] as $item) {
                if (($latest and $item['_id'] == $latest->source_id) or $itemsSaved >= $maxItems) {
                    // It's a descending list
                    // Let's stop
                    break 2;
                }

                try {
                    $this->createDocument($item);
                    $itemsSaved++;

                } catch (Exception $e) {
                    $this->logger->error('Error in saving a NYTimes document: ' . $e->__toString(), [
                        'document' => [
                            '_id' => $item['_id'],
                        ],
                    ]);
                }
            }
            if (count($data['response']['docs']) < 10) {
                break;
            }
        } while ($itemsSaved < $maxItems);
    }

    /**
     * @param  Article  $item
     */
    private function createDocument(array $item): Document
    {
        return DB::transaction(function () use ($item) {
            $image = $this->largestImage($item['multimedia']);
            if (!$image) {
                throw new Exception("Cannot find an image for {$item['_id']}");
            }

            $document = Document::query()->create([
                'source_type' => DocumentSource::NYTIMES,
                'source_id' => $item['_id'],
                'title' => $item['headline']['main'],
                'published_at' => Carbon::parse($item['pub_date']),
                'image' => "https://static01.nyt.com/{$image['url']}",
                'content' => "You can read this article at <a href='{$item['web_url']}' target='_blank'>NYTiems Website</a>.",
            ]);

            if (isset($item['byline']['person'])) {
                $authors = $this->getOrCreateAuthors($item['byline']['person']);
                $document->authors()->sync($authors);
            }

            $tags = $this->getOrCreateTags($item);
            $document->tags()->sync($tags);

            return $document;
        });
    }

    /**
     * @param  Multimedia[]  $items
     * @return Multimedia|null
     */
    private function largestImage(array $items): ?array
    {
        $items = array_filter($items, fn ($item) => $item['type'] === 'image');

        /**
         * @param  Multimedia  $a
         * @param  Multimedia  $b
         */
        usort($items, function (array $a, array $b) {
            return ($b['height'] * $b['width']) - ($a['height'] * $a['width']);
        });

        return count($items) ? $items[0] : null;
    }

    /**
     * @param  Person[]  $persons
     * @return int[]
     */
    private function getOrCreateAuthors(array $persons): array
    {
        $ids = [];
        foreach ($persons as $person) {
            $parts = array_filter([$person['firstname'], $person['middlename'], $person['lastname']]);
            $name = implode(' ', $parts);

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
     * @param  Article  $article
     * @return array<int,array{role:TagRole}>
     */
    private function getOrCreateTags(array $article): array
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

        foreach (['section_name', 'subsection_name'] as $key) {
            if (isset($article[$key])) {
                $getOrCreate($article[$key], TagRole::CATEGORY);
            }
        }

        foreach ($article['keywords'] as $keyword) {
            $getOrCreate($keyword['value'], TagRole::KEYWORD);
        }

        return $ids;
    }
}
