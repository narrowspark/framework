<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory\Provider;

use Interop\Container\ServiceProvider;
use Interop\Http\Factory\RequestFactoryInterface;
use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\Factory\ServerRequestFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Interop\Http\Factory\UploadedFileFactoryInterface;
use Interop\Http\Factory\UriFactoryInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\HttpFactory\RequestFactory;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\HttpFactory\UploadedFileFactory;
use Viserio\Component\HttpFactory\UriFactory;

class HttpFactoryServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices(): array
    {
        return [
            RequestFactoryInterface::class => [self::class, 'createRequestFactory'],
            RequestFactory::class          => function (ContainerInterface $container) {
                return $container->get(RequestFactoryInterface::class);
            },
            ResponseFactoryInterface::class => [self::class, 'createResponseFactory'],
            ResponseFactory::class          => function (ContainerInterface $container) {
                return $container->get(ResponseFactoryInterface::class);
            },
            ServerRequestFactoryInterface::class => [self::class, 'createServerRequestFactory'],
            ServerRequestFactory::class          => function (ContainerInterface $container) {
                return $container->get(ServerRequestFactoryInterface::class);
            },
            StreamFactoryInterface::class => [self::class, 'createStreamFactory'],
            StreamFactory::class          => function (ContainerInterface $container) {
                return $container->get(StreamFactoryInterface::class);
            },
            UploadedFileFactoryInterface::class => [self::class, 'createUploadedFileFactory'],
            UploadedFileFactory::class          => function (ContainerInterface $container) {
                return $container->get(UploadedFileFactoryInterface::class);
            },
            UriFactoryInterface::class => [self::class, 'createUriFactory'],
            UriFactory::class          => function (ContainerInterface $container) {
                return $container->get(UriFactoryInterface::class);
            },
        ];
    }

    public static function createRequestFactory(): RequestFactory
    {
        return new RequestFactory();
    }

    public static function createResponseFactory(): ResponseFactory
    {
        return new ResponseFactory();
    }

    public static function createServerRequestFactory(): ServerRequestFactory
    {
        return new ServerRequestFactory();
    }

    public static function createStreamFactory(): StreamFactory
    {
        return new StreamFactory();
    }

    public static function createUploadedFileFactory(): UploadedFileFactory
    {
        return new UploadedFileFactory();
    }

    public static function createUriFactory(): UriFactory
    {
        return new UriFactory();
    }
}
