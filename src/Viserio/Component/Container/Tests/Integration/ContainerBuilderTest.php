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

use PHPUnit\Framework\TestCase;
use stdClass;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Definition\AliasDefinition;
use Viserio\Contract\Container\Exception\CircularDependencyException;
use Viserio\Contract\Container\Exception\NotFoundException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\ContainerBuilder
 *
 * @small
 */
final class ContainerBuilderTest extends TestCase
{
    /** @var \Viserio\Component\Container\ContainerBuilder */
    protected $containerBuilder;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->containerBuilder = new ContainerBuilder();
    }

    public function testRemoveBoundInstance(): void
    {
        $this->containerBuilder->bind('object', new stdClass());
        $this->containerBuilder->remove('object');

        self::assertFalse($this->containerBuilder->hasDefinition('object'));
    }

    public function testReset(): void
    {
        $this->containerBuilder->bind('test', 'value');
        $this->containerBuilder->reset();

        try {
            $this->containerBuilder->getDefinition('test');
            self::fail('this should not happened');
        } catch (NotFoundException $exception) {
            self::assertSame('You have requested a non-existent service [test].', $exception->getMessage());
        }

        self::assertSame([], $this->containerBuilder->getDefinitions());
    }

    public function testThrowsCircularExceptionForCircularAliases(): void
    {
        $this->expectException(CircularDependencyException::class);
        $this->expectExceptionMessage('Circular reference detected for service [app.test_class]; path: [app.test_class -> stdClass -> app.test_class]');

        $orginal = $alias = stdClass::class;
        $orginal2 = $alias2 = 'app.test_class';

        $this->containerBuilder->bind($orginal);

        $this->containerBuilder->setAliases([
            $alias2 => new AliasDefinition($orginal, $alias2),
            $alias => new AliasDefinition($orginal2, $alias),
        ]);

        $this->containerBuilder->findDefinition($orginal);
    }
}
