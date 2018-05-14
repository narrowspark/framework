<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Provider;

use Psr\Container\ContainerInterface;
use Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider;
use Viserio\Component\Contract\Container\ServiceProvider as ServiceProviderContract;
use Viserio\Component\Contract\Foundation\Kernel as ContractKernel;

class WebServerServiceProvider implements ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            SourceContextProvider::class => [self::class, 'createSourceContextProvider'],
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
     * Create a new FilesystemManager instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Symfony\Component\VarDumper\Dumper\ContextProvider\SourceContextProvider
     */
    public static function createSourceContextProvider(ContainerInterface $container): SourceContextProvider
    {
        return new SourceContextProvider('UTF-8', $container->get(ContractKernel::class)->getRootDir());
    }
}
