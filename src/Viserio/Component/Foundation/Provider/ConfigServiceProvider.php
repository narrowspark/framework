<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Foundation\Config\ParameterProcessor\EnvParameterProcessor;

class ConfigServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            RepositoryContract::class => [self::class, 'extendRepository'],
        ];
    }

    /**
     * Extend viserio config with parameter processor.
     *
     * @param \Psr\Container\ContainerInterface                  $container
     * @param null|\Viserio\Component\Contract\Config\Repository $config
     *
     * @return null|\Viserio\Component\Contract\Config\Repository
     */
    public static function extendRepository(
        ContainerInterface $container,
        ?RepositoryContract $config = null
    ): RepositoryContract {
        if ($config !== null) {
            $config->addParameterProcessor(new EnvParameterProcessor());
        }

        return $config;
    }
}
