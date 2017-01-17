<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\StaticalProxy\StaticalProxy;
use Viserio\Component\StaticalProxy\StaticalProxyResolver;

class StaticalProxyServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            StaticalProxyResolver::class => [self::class, 'createStaticalProxyResolver'],
            'staticalproxy.resolver'     => function (ContainerInterface $container) {
                return $container->get(StaticalProxyResolver::class);
            },
            StaticalProxy::class => [self::class, 'createStaticalProxy'],
            'staticalproxy'      => function (ContainerInterface $container) {
                return $container->get(StaticalProxy::class);
            },
            'facade' => function (ContainerInterface $container) {
                return $container->get(StaticalProxy::class);
            },
        ];
    }

    public static function createStaticalProxy(ContainerInterface $container): StaticalProxy
    {
        StaticalProxy::setContainer($container);

        return new StaticalProxy();
    }

    public static function createStaticalProxyResolver(): StaticalProxyResolver
    {
        return new StaticalProxyResolver();
    }
}
