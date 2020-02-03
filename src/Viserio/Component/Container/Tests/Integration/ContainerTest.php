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

namespace Viserio\Component\Container\Tests\Integration;

use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Viserio\Component\Container\AbstractCompiledContainer;
use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Contract\Container\Exception\NotFoundException;
use Viserio\Contract\Support\Resettable;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\AbstractCompiledContainer
 *
 * @small
 */
final class ContainerTest extends TestCase
{
    /** @var \Viserio\Component\Container\AbstractCompiledContainer */
    protected $abstractContainer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->abstractContainer = new class() extends AbstractCompiledContainer {
        };
    }

    public function testSetThrowsExceptionWithContainerInterfaceKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf('You cannot set service [%s].', ContainerInterface::class));

        $this->abstractContainer->set(ContainerInterface::class, 'test');
    }

    public function testSet(): void
    {
        $this->abstractContainer->set('._. \\o/', $foo = new stdClass());

        self::assertSame($foo, $this->abstractContainer->get('._. \\o/'), '->set() sets a service');
    }

    public function testSetWithNullResetTheService(): void
    {
        $this->abstractContainer->set('foo', new stdClass());
        $this->abstractContainer->set('foo', null);

        self::assertFalse($this->abstractContainer->has('foo'), '->set() with null service resets the service');
    }

    public function testSetReplacesAlias(): void
    {
        $container = new class() extends AbstractCompiledContainer {
            protected array $aliases = [
                'alias' => 'baz',
            ];

            protected array $services = [
                'baz' => 'bar',
            ];
        };

        $container->set('alias', $foo = new stdClass());

        self::assertSame($foo, $container->get('alias'), '->set() replaces an existing alias');
    }

    public function testSetWithNullOnInitializedPredefinedService(): void
    {
        $this->abstractContainer->set('bar', new stdClass());
        $this->abstractContainer->set('bar', null);

        self::assertFalse($this->abstractContainer->has('bar'), '->set() with null service resets the service');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The [bar] service is already initialized, you cannot replace it.');

        $container = new class() extends AbstractCompiledContainer {
            protected array $methodMapping = [
                'bar' => 'bar',
            ];

            protected array $services = [
                'bar' => 'bar',
            ];
        };
        $container->get('bar');
        $container->set('bar', null);

        self::assertTrue($container->has('bar'), '->set() with null service resets the pre-defined service');
    }

    public function testSetWithNullOnUninitializedPredefinedService(): void
    {
        $this->abstractContainer->set('foo', new stdClass());
        $this->abstractContainer->get('foo');
        $this->abstractContainer->set('foo', null);

        self::assertFalse($this->abstractContainer->has('foo'), '->set() with null service resets the service');

        $container = new class() extends AbstractCompiledContainer {
            protected array $methodMapping = [
                'bar' => 'bar',
            ];
        };

        $container->set('bar', null);

        self::assertTrue($container->has('bar'), '->set() with null service resets the pre-defined service');
    }

    public function testGetToThrowExceptionOnNotFoundId(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('You have requested a non-existent service [test].');

        self::assertFalse($this->abstractContainer->get('test'));
    }

    public function testContainerHas(): void
    {
        $container = new class() extends AbstractCompiledContainer {
            protected array $aliases = [
                'bar' => 'baz',
            ];

            protected array $services = [
                'baz' => 'bar',
            ];
        };

        self::assertFalse($container->has('foo'));
        self::assertTrue($container->has('bar'));
    }

    public function testContainerHasParameter(): void
    {
        $container = new class() extends AbstractCompiledContainer {
            protected array $parameters = [
                'baz' => 'bar',
            ];
        };

        self::assertFalse($container->hasParameter('foo'));
        self::assertTrue($container->hasParameter('baz'));
    }

    public function testReset(): void
    {
        $container = new class() extends AbstractCompiledContainer {
            public array $parameters = [
                'baz' => 'bar',
            ];

            public array $services = [];

            public array $delegates = [];

            public function __construct()
            {
                $this->services = [
                    'foo' => new class() implements Resettable {
                        /**
                         * {@inheritdoc}
                         */
                        public function reset(): void
                        {
                        }
                    },
                    'foo2' => new class() implements Resettable {
                        /**
                         * {@inheritdoc}
                         */
                        public function reset(): void
                        {
                            throw new Exception('.');
                        }
                    },
                ];
            }
        };

        $container->delegate($container);

        $container->reset();

        self::assertCount(0, $container->parameters);
        self::assertCount(0, $container->services);
        self::assertCount(0, $container->delegates);
    }

    public function testGetParameterThrowExceptionOnEmptyKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You called getParameter with a empty argument.');

        $this->abstractContainer->getParameter('');
    }

    public function testGetParameterThrowExceptionOnNotFoundKey(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('You have requested a non-existent parameter [bar]. Did you mean one of these: ["baz", "bab"]?');

        $container = new class() extends AbstractCompiledContainer {
            public array $parameters = [
                'baz' => 'bar',
                'bab' => 'bar',
            ];
        };

        $container->getParameter('bar');
    }

    public function testGetParameterThrowExceptionOnNestedNotFoundKey(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('You have requested a non-existent parameter [baz.bar]. You cannot access nested array items, do you want to inject [baz] instead?');

        $container = new class() extends AbstractCompiledContainer {
            public array $parameters = [
                'baz' => [
                    'bab' => 'bar',
                ],
            ];
        };

        $container->getParameter('baz.bar');
    }
}
