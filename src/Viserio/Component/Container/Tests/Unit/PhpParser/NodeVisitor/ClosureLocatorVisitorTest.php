<?php

declare(strict_types=1);

namespace Viserio\Component\Container\Tests\UnitTest\PhpParser\NodeVisitor;

use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Trait_;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;
use Viserio\Component\Container\PhpParser\NodeVisitor\ClosureLocatorVisitor;
use Viserio\Contract\Container\Exception\RuntimeException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\PhpParser\NodeVisitor\ClosureLocatorVisitor
 *
 * @small
 */
final class ClosureLocatorVisitorTest extends TestCase
{
    public function testClosureNodeIsDiscoveredByVisitor(): void
    {
        $closure = function (): void {}; $startLine = __LINE__;

        $reflectedClosure = new ReflectionFunction($closure);
        $closureFinder = new ClosureLocatorVisitor($reflectedClosure);
        $closureNode = new Closure([], ['startLine' => $startLine]);
        $closureFinder->enterNode($closureNode);

        self::assertSame($closureNode, $closureFinder->closureNode);
    }

    public function testClosureNodeIsAmbiguousIfMultipleClosuresOnLine(): void
    {
        $this->expectException(RuntimeException::class);

        $closure = function (): void {}; function (): void {}; $startLine     = __LINE__;

        $closureFinder = new ClosureLocatorVisitor(new ReflectionFunction($closure));
        $closureFinder->enterNode(new Closure([], ['startLine' => $startLine]));
        $closureFinder->enterNode(new Closure([], ['startLine' => $startLine]));
    }

    public function testCalculatesClosureLocation(): void
    {
        $closure = function (): void {};

        $closureFinder = new ClosureLocatorVisitor(new ReflectionFunction($closure));

        $closureFinder->beforeTraverse([]);

        $node = new Namespace_(new Name(['Foo', 'Bar']));
        $closureFinder->enterNode($node);
        $closureFinder->leaveNode($node);

        $node = new Trait_('Fizz');
        $closureFinder->enterNode($node);
        $closureFinder->leaveNode($node);

        $node = new Class_('Buzz');
        $closureFinder->enterNode($node);
        $closureFinder->leaveNode($node);
        $closureFinder->afterTraverse([]);

        $actualLocationKeys = \array_filter($closureFinder->location);
        $expectedLocationKeys = ['directory', 'file', 'function', 'line'];

        self::assertEquals($expectedLocationKeys, \array_keys($actualLocationKeys));
    }

    public function testCanDetermineClassOrTraitInfo(): void
    {
        $closure = function (): void {};

        $closureFinder = new ClosureLocatorVisitor(new ReflectionFunction($closure));
        $closureFinder->location['namespace'] = 'Viserio\Component\Container\Tests\UnitTest\PhpParser\NodeVisitor';
        $closureFinder->location['class'] = 'FooClass';
        $closureFinder->afterTraverse([]);

        self::assertEquals('Viserio\Component\Container\Tests\UnitTest\PhpParser\NodeVisitor\FooClass', $closureFinder->location['namespace'] . '\\' . $closureFinder->location['class']);

        $closureFinder->location['class'] = null;
        $closureFinder->location['trait'] = 'FooTrait';
        $closureFinder->afterTraverse([]);

        self::assertEquals('Viserio\Component\Container\Tests\UnitTest\PhpParser\NodeVisitor\FooTrait', $closureFinder->location['namespace'] . '\\' . $closureFinder->location['trait']);
    }
}
