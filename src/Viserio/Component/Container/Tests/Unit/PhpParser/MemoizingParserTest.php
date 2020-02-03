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

namespace Viserio\Component\Container\Tests\Unit\PhpParser;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use PhpParser\Node;
use PhpParser\Parser;
use Viserio\Component\Container\PhpParser\MemoizingParser;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\PhpParser\MemoizingParser
 *
 * @small
 */
final class MemoizingParserTest extends MockeryTestCase
{
    public function testParse(): void
    {
        /** @var \Mockery\MockInterface|\PhpParser\Parser $wrappedParser */
        $wrappedParser = Mockery::mock(Parser::class);

        $randomCodeStrings = \array_unique(\array_map(
            static function (): string {
                return \uniqid('code', true);
            },
            \range(0, 100)
        ));
        $randomCodeStringsCount = \count($randomCodeStrings);
        $wrappedParser
            ->shouldReceive('parse')
            ->times($randomCodeStringsCount)
            ->andReturnUsing(function (): array {
                return [$this->createMock(Node::class)];
            });

        $parser = new MemoizingParser($wrappedParser);
        $producedNodes = \array_map([$parser, 'parse'], $randomCodeStrings);

        self::assertCount($randomCodeStringsCount, $producedNodes);

        foreach ($producedNodes as $parsed) {
            self::assertCount(1, $parsed);
            self::assertInstanceOf(Node::class, $parsed[0]);
        }

        $nodeIdentifiers = \array_map(
            static function (array $nodes): string {
                return \spl_object_hash($nodes[0]);
            },
            $producedNodes
        );

        self::assertCount(\count($nodeIdentifiers), \array_unique($nodeIdentifiers), 'No duplicate nodes allowed');
        self::assertSame($producedNodes, \array_map([$parser, 'parse'], $randomCodeStrings));
    }
}
