<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Viserio\Component\Container\Container;
use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
use Viserio\Component\HttpFactory\RequestFactory;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\HttpFactory\UploadedFileFactory;
use Viserio\Component\HttpFactory\UriFactory;

/**
 * @internal
 */
final class HttpFactoryServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new HttpFactoryServiceProvider());

        static::assertInstanceOf(RequestFactoryInterface::class, $container->get(RequestFactoryInterface::class));
        static::assertInstanceOf(RequestFactoryInterface::class, $container->get(RequestFactory::class));

        static::assertInstanceOf(ResponseFactoryInterface::class, $container->get(ResponseFactoryInterface::class));
        static::assertInstanceOf(ResponseFactoryInterface::class, $container->get(ResponseFactory::class));

        static::assertInstanceOf(ServerRequestFactoryInterface::class, $container->get(ServerRequestFactoryInterface::class));
        static::assertInstanceOf(ServerRequestFactoryInterface::class, $container->get(ServerRequestFactory::class));

        static::assertInstanceOf(StreamFactoryInterface::class, $container->get(StreamFactoryInterface::class));
        static::assertInstanceOf(StreamFactoryInterface::class, $container->get(StreamFactory::class));

        static::assertInstanceOf(UploadedFileFactoryInterface::class, $container->get(UploadedFileFactoryInterface::class));
        static::assertInstanceOf(UploadedFileFactoryInterface::class, $container->get(UploadedFileFactory::class));

        static::assertInstanceOf(UriFactoryInterface::class, $container->get(UriFactoryInterface::class));
        static::assertInstanceOf(UriFactoryInterface::class, $container->get(UriFactory::class));
    }
}
