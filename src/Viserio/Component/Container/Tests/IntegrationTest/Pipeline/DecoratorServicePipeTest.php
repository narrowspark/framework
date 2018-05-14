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

namespace Viserio\Component\Container\Tests\IntegrationTest\Pipeline;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\ObjectDefinition;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Pipeline\AutowirePipe;
use Viserio\Component\Container\Pipeline\DecoratorServicePipe;
use Viserio\Component\Container\Tests\Fixture\Autowire\Decorated;
use Viserio\Component\Container\Tests\Fixture\Autowire\DecoratedDecorator;
use Viserio\Component\Container\Tests\Fixture\Autowire\Decorator;
use Viserio\Component\Container\Tests\Fixture\Decorator\Bar;
use Viserio\Component\Container\Tests\Fixture\Decorator\Baz;
use Viserio\Component\Container\Tests\Fixture\Decorator\Baz2;
use Viserio\Component\Container\Tests\Fixture\Decorator\Foo;
use Viserio\Component\Container\Tests\Fixture\Decorator\FooInterface;
use Viserio\Component\Container\Tests\Fixture\Decorator\Qux;
use Viserio\Component\Container\Tests\Fixture\Decorator\Qux2;
use Viserio\Contract\Container\Definition\TagAwareDefinition;

/**
 * @internal
 *
 * @small
 */
final class DecoratorServicePipeTest extends TestCase
{
    public function testProcessWithoutAlias(): void
    {
        $container = new ContainerBuilder();
        $fooDefinition = $container
            ->bind(FooInterface::class, Foo::class)
            ->setPublic(false);
        /** @var ObjectDefinition $fooExtendedDefinition */
        $fooExtendedDefinition = $container
            ->bind('foo.extended', Baz::class)
            ->decorate(FooInterface::class)
            ->setPublic(true);

        $barDefinition = $container
            ->bind(Bar::class)
            ->setPublic(true);
        /** @var ObjectDefinition $barExtendedDefinition */
        $barExtendedDefinition = $container
            ->bind('bar.extended', Qux::class)
            ->decorate(Bar::class, 'bar.yoo')
            ->setPublic(true);

        $this->process($container);

        $extendedFooAlias = $container->getAlias(FooInterface::class);

        self::assertEquals('foo.extended', $extendedFooAlias->getName());
        self::assertFalse($extendedFooAlias->isPublic());

        $extendedBarAlias = $container->getAlias(Bar::class);

        self::assertEquals('bar.extended', $extendedBarAlias->getName());
        self::assertTrue($extendedBarAlias->isPublic());

        $extendedInnerFoo = $container->getDefinition('foo.extended.inner');

        self::assertSame($fooDefinition, $extendedInnerFoo);
        self::assertFalse($extendedInnerFoo->isPublic());

        $barYoo = $container->getDefinition('bar.yoo');

        self::assertSame($barDefinition, $barYoo);
        self::assertFalse($barYoo->isPublic());

        self::assertNull($fooExtendedDefinition->getDecorator());
        self::assertNull($barExtendedDefinition->getDecorator());
    }

    public function testProcessWithAlias(): void
    {
        $container = new ContainerBuilder();
        $container
            ->bind(FooInterface::class, Foo::class)
            ->setPublic(true);

        $container->setAlias(FooInterface::class, 'foo.alias');

        $fooExtendedDefinition = $container
            ->bind('foo.extended', Baz::class)
            ->decorate('foo.alias')
            ->setPublic(true);

        $this->process($container);

        $fooExtendedAlias = $container->getAlias('foo.alias');

        self::assertEquals('foo.extended', $fooExtendedAlias->getName());
        self::assertFalse($fooExtendedAlias->isPublic());

        self::assertEquals(FooInterface::class, $container->getAlias('foo.extended.inner')->getName());
        self::assertFalse($container->getAlias('foo.extended.inner')->isPublic());
        self::assertNull($fooExtendedDefinition->getDecorator());
    }

    public function testProcessWithPriority(): void
    {
        $container = new ContainerBuilder();
        $fooDefinition = $container
            ->bind(FooInterface::class, Foo::class)
            ->setPublic(false);

        $barDefinition = $container
            ->bind(Bar::class)
            ->decorate(FooInterface::class)
            ->setPublic(true);

        $bazDefinition = $container
            ->bind(Baz2::class)
            ->decorate(FooInterface::class, null, 5)
            ->setPublic(true);

        $quxDefinition = $container
            ->bind(Qux2::class)
            ->decorate(FooInterface::class, null, 3)
            ->setPublic(true);

        $this->process($container);

        $barAlias = $container->getAlias(FooInterface::class);

        self::assertEquals(Bar::class, $barAlias->getName());
        self::assertFalse($barAlias->isPublic());

        $bazInnerDefinition = $container->getDefinition(Baz2::class . '.inner');

        self::assertSame($fooDefinition, $bazInnerDefinition);
        self::assertFalse($bazInnerDefinition->isPublic());

        $quxAlias = $container->getAlias(Qux2::class . '.inner');

        self::assertEquals(Baz2::class, $quxAlias->getName());
        self::assertFalse($quxAlias->isPublic());

        self::assertNull($barDefinition->getDecorator());
        self::assertNull($bazDefinition->getDecorator());
        self::assertNull($quxDefinition->getDecorator());
    }

    public function testProcessMovesTagsFromDecoratedDefinitionToDecoratingDefinition(): void
    {
        $container = new ContainerBuilder();
        $container
            ->bind(FooInterface::class, Foo::class)
            ->setTags(['bar' => ['attr' => 'baz']]);
        $container
            ->bind(Baz::class)
            ->decorate(FooInterface::class)
            ->setTags(['foobar' => ['attr' => 'bar']]);

        $this->process($container);

        /** @var TagAwareDefinition $def */
        $def = $container->getDefinition(Baz::class . '.inner');
        /** @var TagAwareDefinition $def2 */
        $def2 = $container->getDefinition(Baz::class);

        self::assertEmpty($def->getTags());
        self::assertEquals(['bar' => ['attr' => 'baz'], 'foobar' => ['attr' => 'bar']], $def2->getTags());
    }

    public function testProcessMovesTagsFromDecoratedDefinitionToDecoratingDefinitionMultipleTimes(): void
    {
        $container = new ContainerBuilder();
        $container
            ->bind(FooInterface::class, Foo::class)
            ->setTags(['bar' => ['attr' => 'baz']])
            ->setPublic(true);
        $container
            ->bind(Baz2::class)
            ->decorate(FooInterface::class, null, 50);
        $container
            ->bind(Qux2::class)
            ->decorate(FooInterface::class, null, 2);

        $this->process($container);

        /** @var TagAwareDefinition $def */
        $def = $container->getDefinition(Baz2::class);
        /** @var TagAwareDefinition $def2 */
        $def2 = $container->getDefinition(Qux2::class);

        self::assertEmpty($def->getTags());
        self::assertEquals(['bar' => ['attr' => 'baz']], $def2->getTags());
    }

    public function testAutowireDecorator(): void
    {
        $container = new ContainerBuilder();
        $container->bind(LoggerInterface::class, NullLogger::class);
        $container->bind(Decorated::class);
        $container->bind(Decorator::class)
            ->decorate(Decorated::class);

        $this->process($container);

        /** @var ObjectDefinition $definition */
        $definition = $container->getDefinition(Decorator::class);
        /** @var ReferenceDefinition $ref */
        $ref = $definition->getArgument(1);

        self::assertSame(Decorator::class . '.inner', $ref->getName());
    }

    public function testAutowireDecoratorChain(): void
    {
        $container = new ContainerBuilder();
        $container->bind(LoggerInterface::class, NullLogger::class);
        $container->bind(Decorated::class, Decorated::class);
        $container
            ->bind(Decorator::class, Decorator::class)
            ->decorate(Decorated::class);
        $container
            ->bind(DecoratedDecorator::class, DecoratedDecorator::class)
            ->decorate(Decorated::class);

        $this->process($container);

        /** @var ObjectDefinition $definition */
        $definition = $container->getDefinition(DecoratedDecorator::class);
        /** @var ReferenceDefinition $ref */
        $ref = $definition->getArgument(0);

        self::assertSame(DecoratedDecorator::class . '.inner', $ref->getName());
    }

    public function testAutowireDecoratorRenamedId(): void
    {
        $container = new ContainerBuilder();
        $container->bind(LoggerInterface::class, NullLogger::class);
        $container->bind(Decorated::class, Decorated::class);
        $container
            ->bind(Decorator::class, Decorator::class)
            ->decorate(Decorated::class, 'renamed');

        $this->process($container);

        /** @var ObjectDefinition $definition */
        $definition = $container->getDefinition(Decorator::class);
        /** @var ReferenceDefinition $ref */
        $ref = $definition->getArgument(1);

        self::assertSame('renamed', $ref->getName());
    }

    protected function process(ContainerBuilder $container): void
    {
        foreach ([new DecoratorServicePipe(), new AutowirePipe()] as $pipe) {
            $pipe->process($container);
        }
    }
}
