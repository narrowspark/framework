<?php
declare(strict_types=1);
namespace Viserio\HttpFactory\Tests\Providers;

use Interop\Http\Factory\RequestFactoryInterface;
use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\Factory\ServerRequestFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Interop\Http\Factory\UploadedFileFactoryInterface;
use Interop\Http\Factory\UriFactoryInterface;
use PHPUnit\Framework\TestCase;
use Viserio\Container\Container;
use Viserio\HttpFactory\Providers\HttpFactoryServiceProvider;
use Viserio\HttpFactory\RequestFactory;
use Viserio\HttpFactory\ResponseFactory;
use Viserio\HttpFactory\ServerRequestFactory;
use Viserio\HttpFactory\StreamFactory;
use Viserio\HttpFactory\UploadedFileFactory;
use Viserio\HttpFactory\UriFactory;

class HttpFactoryServiceProviderTest extends TestCase
{
    public function testProvider()
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
