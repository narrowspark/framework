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

namespace Viserio\Component\Container\Tests\Unit\PhpParser\NodeVisitor;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Container\PhpParser\NodeVisitor\MagicConstantVisitor;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\PhpParser\NodeVisitor\MagicConstantVisitor
 *
 * @small
 */
final class MagicConstantVisitorTest extends MockeryTestCase
{
    public static function provideDataFromClosureLocationGetsUsedCases(): iterable
    {
        return [
            ['PhpParser\Node\Scalar\MagicConst\Class_', 'PhpParser\Node\Scalar\String_'],
            ['PhpParser\Node\Scalar\MagicConst\Dir', 'PhpParser\Node\Scalar\String_'],
            ['PhpParser\Node\Scalar\MagicConst\File', 'PhpParser\Node\Scalar\String_'],
            ['PhpParser\Node\Scalar\MagicConst\Function_', 'PhpParser\Node\Scalar\String_'],
            ['PhpParser\Node\Scalar\MagicConst\Line', 'PhpParser\Node\Scalar\LNumber'],
            ['PhpParser\Node\Scalar\MagicConst\Method', 'PhpParser\Node\Scalar\String_'],
            ['PhpParser\Node\Scalar\MagicConst\Namespace_', 'PhpParser\Node\Scalar\String_'],
            ['PhpParser\Node\Scalar\MagicConst\Trait_', 'PhpParser\Node\Scalar\String_'],
            ['PhpParser\Node\Scalar\String_', 'PhpParser\Node\Scalar\String_'],
        ];
    }

    /**
     * @dataProvider provideDataFromClosureLocationGetsUsedCases
     */
    public function testDataFromClosureLocationGetsUsed(string $original, string $result): void
    {
        $location = [
            'class' => null,
            'directory' => null,
            'file' => null,
            'function' => null,
            'line' => null,
            'method' => null,
            'namespace' => null,
            'trait' => null,
        ];

        $visitor = new MagicConstantVisitor($location);
        $node = $this->getMockParserNode($original, \str_replace('\\', '_', \substr(\rtrim($original, '_'), 15)));
        $resultNode = $visitor->leaveNode($node) ?: $node;

        self::assertInstanceOf($result, $resultNode);
    }

    /**
     * @return \Mockery\MockInterface|\PhpParser\Node\Scalar\MagicConst
     */
    public function getMockParserNode(string $class, ?string $type = null, ?string $attribute = null)
    {
        $mock = Mockery::mock($class);
        $mock->shouldReceive('getAttribute')
            ->andReturn($attribute);

        $mock->shouldReceive('getType')
            ->andReturn($type);

        return $mock;
    }
}
