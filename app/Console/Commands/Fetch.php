<?php

namespace App\Console\Commands;

use App\Enums\DocumentSource;
use Illuminate\Console\Command;

class Fetch extends Command
{
    protected $signature = 'fetch
        {--source=}
        {--max-items=200}
    ';

    protected $description = 'Command description';

    public function handle()
    {
        $maxItems = intval($this->option('max-items'));
        $source = $this->getSource();
        $handler = $source->getHandler();
        $handler->fetch($maxItems, false);
    }

    private function getSource(): DocumentSource
    {
        $source = $this->option('source');
        if (!$source) {
            $availables = array_column(DocumentSource::cases(), 'value');
            $this->fail('You should specify --source with one of these values: ' . implode(',', $availables));
        }

        return DocumentSource::from($source);
    }
}
