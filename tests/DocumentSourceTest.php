<?php

namespace Tests;

use App\Enums\DocumentSource;
use App\Sources\ESPN;
use App\Sources\Guardian;
use App\Sources\NYTimes;

class DocumentSourceTest extends TestCase
{
    public function test_get_handler()
    {
        $handler = DocumentSource::GUARDIAN->getHandler();
        $this->assertInstanceOf(Guardian::class, $handler);

        $handler = DocumentSource::NYTIMES->getHandler();
        $this->assertInstanceOf(NYTimes::class, $handler);

        $handler = DocumentSource::ESPN->getHandler();
        $this->assertInstanceOf(ESPN::class, $handler);
    }
}
