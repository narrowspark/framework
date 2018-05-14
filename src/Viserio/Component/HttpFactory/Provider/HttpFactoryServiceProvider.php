<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory\Provider;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Viserio\Component\Contract\Container\ServiceProvider as ServiceProviderContract;
use Viserio\Component\HttpFactory\RequestFactory;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\HttpFactory\UploadedFileFactory;
use Viserio\Component\HttpFactory\UriFactory;

class HttpFactoryServiceProvider implements ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
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

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [];
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
