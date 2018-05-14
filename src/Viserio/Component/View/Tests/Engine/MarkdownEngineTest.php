<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
