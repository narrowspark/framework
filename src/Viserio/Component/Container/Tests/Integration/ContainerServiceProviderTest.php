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

namespace Viserio\Component\Container\Tests\Integration;

use stdClass;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Component\Container\Tests\Fixture\ServiceProvider\ExtendingFixtureServiceProvider;
use Viserio\Component\Container\Tests\Fixture\ServiceProvider\MethodCallsServiceProvider;
use Viserio\Component\Container\Tests\Fixture\ServiceProvider\ServiceFixture;
use Viserio\Component\Container\Tests\Fixture\ServiceProvider\SimpleFixtureServiceProvider;
use Viserio\Component\Container\Tests\Fixture\ServiceProvider\SimpleTaggedServiceProvider;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\ContainerBuilder
 *
 * @small
 */
final class ContainerServiceProviderTest extends AbstractContainerTestCase
{
    protected const DUMP_CLASS_CONTAINER = false;

    protected const SKIP_TEST_PIPE = true;

    public function testContainerCanBeDumpedWithRegisteredProviderServices(): void
    {
        $this->containerBuilder->register(new SimpleFixtureServiceProvider());
        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertEquals('value', $this->container->getParameter('param'));
        self::assertInstanceOf(ServiceFixture::class, $this->container->get('service'));
    }

    public function testServiceProviderCanAddTagsToContainerBuilder(): void
    {
        $this->containerBuilder->register(new SimpleTaggedServiceProvider());

        $this->containerBuilder->compile();

        foreach ($this->containerBuilder->getTagged('test') as $item) {
            self::assertSame(stdClass::class, $item[0]->getValue());
        }
    }

    public function testContainerCanBeDumpedWithServiceProviderThatHasMethodCalls(): void
    {
        $this->containerBuilder->register(new MethodCallsServiceProvider(), [
            'anotherParameter' => 'anotherValue',
        ]);

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertInstanceOf(ServiceFixture::class, $this->container->get('service'));
    }

    public function testContainerCanBeDumpedWithExtendService(): void
    {
        $this->containerBuilder->bind('previous', stdClass::class)
            ->setPublic(true);
        $this->containerBuilder->register(new SimpleFixtureServiceProvider());

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertEquals('foofoo', $this->container->get('previous')->foo);
    }

    public function testContainerCanBeDumpedWithNotFoundExtendService(): void
    {
        $this->containerBuilder->register(new ExtendingFixtureServiceProvider());

        $this->containerBuilder->compile();

        $this->assertDumpedContainer(__FUNCTION__);

        self::assertFalse($this->container->has('previous'));
    }

    /**
     * {@inheritdoc}
     */
    protected function assertDumpedContainer(?string $functionName): void
    {
        $containerBuilder = $this->containerBuilder;

        $this->dumpContainer($functionName);

        $this->containerBuilder = $containerBuilder;

        parent::assertDumpedContainer($functionName);
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
