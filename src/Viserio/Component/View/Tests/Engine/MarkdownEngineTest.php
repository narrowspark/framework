<?php
declare(strict_types=1);
namespace Viserio\Component\View\Tests\Engines;

use Parsedown;
use ParsedownExtra;
use PHPUnit\Framework\TestCase;
use Viserio\Component\View\Engine\MarkdownEngine;

class MarkdownEngineTest extends TestCase
{
    public function testGetWithParsedown()
    {
        $parser = new MarkdownEngine(new Parsedown());

        self::assertSame('<p><a href="google.com">test</a></p>', $parser->get(['path' => __DIR__ . '/../Fixture/foo.md']));
    }

    public function testGetWithParsedownExtra()
    {
        $parser = new MarkdownEngine(new ParsedownExtra());

        self::assertSame('<p><a href="google.com">test</a></p>', $parser->get(['path' => __DIR__ . '/../Fixture/foo.md']));
    }
}
