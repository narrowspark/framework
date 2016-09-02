<?php
declare(strict_types=1);
namespace Viserio\Cookie\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Cookie\CookieJar;
use Viserio\Cookie\RequestCookie;
use Viserio\Config\Manager as ConfigManager;

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
            'cookie' => function(ContainerInterface $container) {
                return $container->get(CookieJar::class);
            },
            'request-cookie' => function(ContainerInterface $container) {
                return $container->get(RequestCookie::class);
            },
            'cookie.options' => [self::class, 'createOptions'],
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
            $config = self::get($container, 'cookie.options');
        }

        return (new CookieJar())->setDefaultPathAndDomain(
            $config['path'],
            $config['domain'],
            $config['secure']
        );
    }

    public static function createOptions(ContainerInterface $container) : array
    {
        return [
            'path' => self::get($container, 'path'),
            'domain' => self::get($container, 'domain'),
            'secure' => self::get($container, 'secure'),
        ];
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
        $namespacedName = self::PACKAGE.'.'.$name;

        return $container->has($namespacedName) ? $container->get($namespacedName) :
            ($container->has($name) ? $container->get($name) : $default);
    }
}
