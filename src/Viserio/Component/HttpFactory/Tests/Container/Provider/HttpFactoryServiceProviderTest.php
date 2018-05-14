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

namespace Viserio\Component\HttpFactory\Tests\Container\Provider;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tester\AbstractContainerTestCase;
use Viserio\Component\HttpFactory\Container\Provider\HttpFactoryServiceProvider;
use Viserio\Component\HttpFactory\RequestFactory;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\HttpFactory\UploadedFileFactory;
use Viserio\Component\HttpFactory\UriFactory;

/**
 * @internal
 *
 * @small
 */
final class HttpFactoryServiceProviderTest extends AbstractContainerTestCase
{
    public function testProvider(): void
    {
        self::assertInstanceOf(RequestFactoryInterface::class, $this->container->get(RequestFactoryInterface::class));
        self::assertInstanceOf(RequestFactoryInterface::class, $this->container->get(RequestFactory::class));

        self::assertInstanceOf(ResponseFactoryInterface::class, $this->container->get(ResponseFactoryInterface::class));
        self::assertInstanceOf(ResponseFactoryInterface::class, $this->container->get(ResponseFactory::class));

        self::assertInstanceOf(ServerRequestFactoryInterface::class, $this->container->get(ServerRequestFactoryInterface::class));
        self::assertInstanceOf(ServerRequestFactoryInterface::class, $this->container->get(ServerRequestFactory::class));

        self::assertInstanceOf(StreamFactoryInterface::class, $this->container->get(StreamFactoryInterface::class));
        self::assertInstanceOf(StreamFactoryInterface::class, $this->container->get(StreamFactory::class));

        self::assertInstanceOf(UploadedFileFactoryInterface::class, $this->container->get(UploadedFileFactoryInterface::class));
        self::assertInstanceOf(UploadedFileFactoryInterface::class, $this->container->get(UploadedFileFactory::class));

        self::assertInstanceOf(UriFactoryInterface::class, $this->container->get(UriFactoryInterface::class));
        self::assertInstanceOf(UriFactoryInterface::class, $this->container->get(UriFactory::class));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(new HttpFactoryServiceProvider());
    }

    /**
     * {@inheritdoc}
     */
    protected function getDumpFolderPath(): string
    {
        return __DIR__ . \DIRECTORY_SEPARATOR . 'Compiled';
    }

    /**
     * {@inheritdoc}
     */
    protected function getNamespace(): string
    {
        return __NAMESPACE__ . '\\Compiled';
    }
}
