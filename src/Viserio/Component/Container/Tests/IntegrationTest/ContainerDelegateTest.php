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

namespace Viserio\Component\Container\Tests\IntegrationTest;

use Mouf\Picotainer\Picotainer;
use stdClass;
use Viserio\Component\Container\AbstractCompiledContainer;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Test\AbstractContainerTestCase;

/**
 * @internal
 *
 * @property AbstractCompiledContainer $container
 *
 * @small
 */
final class ContainerDelegateTest extends AbstractContainerTestCase
{
    protected const DUMP_CLASS_CONTAINER = false;

    public function testAliasToDependencyInDelegateContainer(): void
    {
        $delegate = new Picotainer([
            'instance' => function () {
                return 'this is a value';
            },
        ]);

        $this->containerBuilder->bind('instance2', stdClass::class)
            ->setProperties(['ff' => new ReferenceDefinition('instance', ReferenceDefinition::DELEGATE_REFERENCE)]);

        $this->containerBuilder->compile();

        $this->dumpContainer($functionName = __FUNCTION__);
        $this->assertDumpedContainer($functionName);

        $this->container->setDelegates([$delegate]);
        $this->container->delegate(new Picotainer([]));

        self::assertTrue($this->container->hasInDelegate('instance'));
        self::assertSame('this is a value', $this->container->get('instance2')->ff);
        self::assertTrue($this->container->hasInDelegate('instance'));
        self::assertFalse($this->container->hasInDelegate('instance3'));
    }

    public function testWithContainerCall(): void
    {
        $this->containerBuilder->compile();

        $this->dumpContainer(__FUNCTION__);

        $value = new stdClass();

        $delegate = new Picotainer([
            'stdClass' => function () use ($value) {
                return $value;
            },
        ]);

        $this->container->setDelegates([$delegate]);

        $result = $this->container->call(function (stdClass $foo) {
            return $foo;
        });

        self::assertSame($value, $result, 'The root container was not used for the type-hint');
    }

    /**
     * {@inheritdoc}
     */
    protected function getDumpFolderPath(): string
    {
        return \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Compiled' . \DIRECTORY_SEPARATOR;
    }

    /**
     * {@inheritdoc}
     */
    protected function getNamespace(): string
    {
        return __NAMESPACE__ . '\\Compiled';
    }
}
