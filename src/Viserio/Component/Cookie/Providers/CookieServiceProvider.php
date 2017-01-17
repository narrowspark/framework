<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Contracts\Cookie\QueueingFactory as JarContract;
use Viserio\Component\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Component\Cookie\CookieJar;

class CookieServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    public const PACKAGE = 'viserio.cookie';

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
