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

/**
 * @internal
 */
final class HttpFactoryServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new HttpFactoryServiceProvider());

        $this->assertInstanceOf(RequestFactoryInterface::class, $container->get(RequestFactoryInterface::class));
        $this->assertInstanceOf(RequestFactoryInterface::class, $container->get(RequestFactory::class));

        $this->assertInstanceOf(ResponseFactoryInterface::class, $container->get(ResponseFactoryInterface::class));
        $this->assertInstanceOf(ResponseFactoryInterface::class, $container->get(ResponseFactory::class));

        $this->assertInstanceOf(ServerRequestFactoryInterface::class, $container->get(ServerRequestFactoryInterface::class));
        $this->assertInstanceOf(ServerRequestFactoryInterface::class, $container->get(ServerRequestFactory::class));

        $this->assertInstanceOf(StreamFactoryInterface::class, $container->get(StreamFactoryInterface::class));
        $this->assertInstanceOf(StreamFactoryInterface::class, $container->get(StreamFactory::class));

        $this->assertInstanceOf(UploadedFileFactoryInterface::class, $container->get(UploadedFileFactoryInterface::class));
        $this->assertInstanceOf(UploadedFileFactoryInterface::class, $container->get(UploadedFileFactory::class));

        $this->assertInstanceOf(UriFactoryInterface::class, $container->get(UriFactoryInterface::class));
        $this->assertInstanceOf(UriFactoryInterface::class, $container->get(UriFactory::class));
    }
}
