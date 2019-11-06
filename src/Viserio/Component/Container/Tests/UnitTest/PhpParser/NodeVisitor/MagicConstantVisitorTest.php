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

namespace Viserio\Component\Container\Tests\UnitTest\PhpParser\NodeVisitor;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Container\PhpParser\NodeVisitor\MagicConstantVisitor;

/**
 * @internal
 *
 * @small
 */
final class MagicConstantVisitorTest extends MockeryTestCase
{
    public function provideDataFromClosureLocationGetsUsedCases(): iterable
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
     *
     * @param string $original
     * @param string $result
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
     * @param string      $class
     * @param null|string $type
     * @param null|string $attribute
     *
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
