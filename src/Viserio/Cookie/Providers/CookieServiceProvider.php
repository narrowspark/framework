<?php
declare(strict_types=1);
namespace Viserio\Cookie\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Contracts\Cookie\QueueingFactory as JarContract;
use Viserio\Cookie\CookieJar;
use Viserio\Cookie\RequestCookie;

class CookieServiceProvider implements ServiceProvider
{
    const PACKAGE = 'viserio.cookie';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            CookieJar::class => [self::class, 'createCookieJar'],
            RequestCookie::class => [self::class, 'createRequestCookie'],
            'cookie' => function (ContainerInterface $container) {
                return $container->get(CookieJar::class);
            },
            JarContract::class => function (ContainerInterface $container) {
                return $container->get(CookieJar::class);
            },
            'request-cookie' => function (ContainerInterface $container) {
                return $container->get(RequestCookie::class);
            }
        ];
    }

    public static function createRequestCookie(): RequestCookie
    {
        return new RequestCookie();
    }

    public static function createCookieJar(ContainerInterface $container): CookieJar
    {
        if ($container->has(ConfigManager::class)) {
            $config = $container->get(ConfigManager::class)->get('session');
        } else {
            $config = self::get($container, 'options');
        }

        return (new CookieJar())->setDefaultPathAndDomain(
            $config['path'],
            $config['domain'],
            $config['secure']
        );
    }

    /**
     * Returns the entry named PACKAGE.$name, of simply $name if PACKAGE.$name is not found.
     *
     * @param ContainerInterface $container
     * @param string             $name
     *
     * @return mixed
     */
    private static function get(ContainerInterface $container, string $name, $default = null)
    {
        $namespacedName = self::PACKAGE . '.' . $name;

        return $container->has($namespacedName) ? $container->get($namespacedName) :
            ($container->has($name) ? $container->get($name) : $default);
    }
}
