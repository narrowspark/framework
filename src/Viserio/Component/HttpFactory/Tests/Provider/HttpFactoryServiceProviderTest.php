<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory\Tests\Provider;

use Interop\Http\Factory\RequestFactoryInterface;
use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\Factory\ServerRequestFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Interop\Http\Factory\UploadedFileFactoryInterface;
use Interop\Http\Factory\UriFactoryInterface;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\HttpFactory\Provider\HttpFactoryServiceProvider;
use Viserio\Component\HttpFactory\RequestFactory;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\HttpFactory\UploadedFileFactory;
use Viserio\Component\HttpFactory\UriFactory;

class HttpFactoryServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new HttpFactoryServiceProvider());

        self::assertInstanceOf(RequestFactoryInterface::class, $container->get(RequestFactoryInterface::class));
        self::assertInstanceOf(RequestFactoryInterface::class, $container->get(RequestFactory::class));

        self::assertInstanceOf(ResponseFactoryInterface::class, $container->get(ResponseFactoryInterface::class));
        self::assertInstanceOf(ResponseFactoryInterface::class, $container->get(ResponseFactory::class));

        self::assertInstanceOf(ServerRequestFactoryInterface::class, $container->get(ServerRequestFactoryInterface::class));
        self::assertInstanceOf(ServerRequestFactoryInterface::class, $container->get(ServerRequestFactory::class));

        self::assertInstanceOf(StreamFactoryInterface::class, $container->get(StreamFactoryInterface::class));
        self::assertInstanceOf(StreamFactoryInterface::class, $container->get(StreamFactory::class));

        self::assertInstanceOf(UploadedFileFactoryInterface::class, $container->get(UploadedFileFactoryInterface::class));
        self::assertInstanceOf(UploadedFileFactoryInterface::class, $container->get(UploadedFileFactory::class));

        self::assertInstanceOf(UriFactoryInterface::class, $container->get(UriFactoryInterface::class));
        self::assertInstanceOf(UriFactoryInterface::class, $container->get(UriFactory::class));
    }
}
