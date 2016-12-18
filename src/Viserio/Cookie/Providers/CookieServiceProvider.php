<?php
declare(strict_types=1);
namespace Viserio\Cookie\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\Cookie\QueueingFactory as JarContract;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Cookie\CookieJar;

class CookieServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    const PACKAGE = 'viserio.cookie';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            JarContract::class => [self::class, 'createCookieJar'],
            'cookie'           => function (ContainerInterface $container) {
                return $container->get(JarContract::class);
            },
            CookieJar::class => function (ContainerInterface $container) {
                return $container->get(JarContract::class);
            },
        ];
    }

    public static function createCookieJar(ContainerInterface $container): CookieJar
    {
        return (new CookieJar())->setDefaultPathAndDomain(
            self::getConfig($container, 'path', ''),
            self::getConfig($container, 'domain', ''),
            self::getConfig($container, 'secure', true)
        );
    }
}
