<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Symfony\Component\VarDumper\Server\Connection;
use Symfony\Component\VarDumper\Server\DumpServer;
use Symfony\Component\VarDumper\VarDumper;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Component\WebServer\RequestContextProvider;

class WebServerServiceProvider implements
    ServiceProviderInterface,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
{
    use OptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            RequestContextProvider::class => [self::class, 'createRequestContextProvider'],
            Connection::class             => [self::class, 'createVarDumpConnection'],
            DumpServer::class             => [self::class, 'createDumpServer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return ['viserio', 'webserver'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        return [
            'debug_server' => [
                'host' => 'tcp://127.0.0.1:9912',
            ],
        ];
    }

    /**
     * Create a new RequestContextProvider instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\WebServer\RequestContextProvider
     */
    public static function createRequestContextProvider(ContainerInterface $container): RequestContextProvider
    {
        return new RequestContextProvider($container->get(ServerRequestInterface::class));
    }

    /**
     * Create a new VarDump Connection instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Symfony\Component\VarDumper\Server\Connection
     */
    public static function createVarDumpConnection(ContainerInterface $container): Connection
    {
        $resolvedOptions  = self::resolveOptions($container->get('config'));
        $contextProviders = [];

        if ($container->has(ServerRequestInterface::class) && $container->has(RequestContextProvider::class)) {
            $contextProviders['request'] = $container->get(RequestContextProvider::class);
        }

        if ($container->has(SourceContextProvider::class)) {
            $contextProviders['source'] = $container->get(SourceContextProvider::class);
        }

        return new Connection(
            $resolvedOptions['debug_server']['host'],
            $contextProviders
        );
    }

    /**
     * Create a new DumpServer instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Symfony\Component\VarDumper\Server\DumpServer
     */
    public static function createDumpServer(ContainerInterface $container): DumpServer
    {
        $connection = $container->get(Connection::class);
        // @codeCoverageIgnoreStart
        VarDumper::setHandler(static function ($var) use ($connection): void {
            $data = (new VarCloner())->cloneVar($var);

            if ($connection->write($data)) {
                (new CliDumper())->dump($data);
            }
        });
        // @codeCoverageIgnoreEnd
        $logger = null;

        if ($container->has(LoggerInterface::class)) {
            $logger = $container->get(LoggerInterface::class);
        }

        $resolvedOptions = self::resolveOptions($container->get('config'));

        return new DumpServer(
            $resolvedOptions['debug_server']['host'],
            $logger
        );
    }
}
