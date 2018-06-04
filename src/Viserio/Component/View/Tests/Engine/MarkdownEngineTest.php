<?php
declare(strict_types=1);
namespace Viserio\Component\View\Tests\Engines;

use Parsedown;
use ParsedownExtra;
use PHPUnit\Framework\TestCase;
use Viserio\Component\View\Engine\MarkdownEngine;

/**
 * @internal
 */
final class MarkdownEngineTest extends TestCase
{
    public function testGetWithParsedown(): void
    {
        $parser = new MarkdownEngine(new Parsedown());

        $this->assertSame('<p><a href="google.com">test</a></p>', $parser->get(['path' => __DIR__ . '/../Fixture/foo.md']));
    }

    public function testGetWithParsedownExtra(): void
    {
        $parser = new MarkdownEngine(new ParsedownExtra());

        $this->assertSame('<p><a href="google.com">test</a></p>', $parser->get(['path' => __DIR__ . '/../Fixture/foo.md']));
    }
}
