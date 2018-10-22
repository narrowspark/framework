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

        static::assertSame('<p><a href="google.com">test</a></p>', $parser->get(['path' => \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'foo.md']));
    }

    public function testGetWithParsedownExtra(): void
    {
        $parser = new MarkdownEngine(new ParsedownExtra());

        static::assertSame('<p><a href="google.com">test</a></p>', $parser->get(['path' => \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'foo.md']));
    }
}
