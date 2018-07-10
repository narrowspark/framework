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
use Viserio\Component\Contract\Foundation\Kernel as ContractKernel;
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
        $config = [];

        if (\class_exists(VarDumper::class)) {
            $config[RequestContextProvider::class] = [self::class, 'createRequestContextProvider'];
            $config[SourceContextProvider::class]  = [self::class, 'createSourceContextProvider'];

            $config[Connection::class] = [self::class, 'createVarDumpConnection'];
            $config[DumpServer::class] = [self::class, 'createDumpServer'];
        }

        return $config;
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
        $config = [];

        if (\class_exists(VarDumper::class)) {
            $config = [
                'debug_server' => [
                    'host' => 'tcp://127.0.0.1:9912',
                ],
            ];
        }

        return $config;
    }

    /**
     * Create a new RequestContextProvider instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return null|\Viserio\Component\WebServer\RequestContextProvider
     */
    public static function createRequestContextProvider(ContainerInterface $container): ?RequestContextProvider
    {
        if ($container->has(ServerRequestInterface::class)) {
            return new RequestContextProvider($container->get(ServerRequestInterface::class));
        }

        return null;
    }

    /**
     * Create a new FilesystemManager instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return null|\Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider
     */
    public static function createSourceContextProvider(ContainerInterface $container): ?SourceContextProvider
    {
        if ($container->has(ContractKernel::class)) {
            return new SourceContextProvider('utf-8', $container->get(ContractKernel::class)->getRootDir());
        }

        return null;
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

        if ($container->has(ContractKernel::class) && $container->has(SourceContextProvider::class)) {
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

        VarDumper::setHandler(function ($var) use ($connection) {
            $data = (new VarCloner())->cloneVar($var);

            if ($connection->write($data)) {
                (new CliDumper())->dump($data);
            }
        });

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
