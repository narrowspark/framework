<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\View\Tests\Engines;

use Parsedown;
use ParsedownExtra;
use PHPUnit\Framework\TestCase;
use Viserio\Component\View\Engine\MarkdownEngine;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class MarkdownEngineTest extends TestCase
{
    public function testGetWithParsedown(): void
    {
        $parser = new MarkdownEngine(new Parsedown());

        self::assertSame('<p><a href="example.com">test</a></p>', $parser->get(['path' => \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'foo.md']));
    }

    public function testGetWithParsedownExtra(): void
    {
        $parser = new MarkdownEngine(new ParsedownExtra());

        self::assertSame('<p><a href="example.com">test</a></p>', $parser->get(['path' => \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'foo.md']));
    }
}
