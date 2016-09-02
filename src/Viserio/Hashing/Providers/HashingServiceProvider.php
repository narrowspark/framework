<?php
declare(strict_types=1);
namespace Viserio\Hashing\Providers;

use Defuse\Crypto\Key;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Hashing\Password;

class HashingServiceProvider implements ServiceProvider
{
    const PACKAGE = 'viserio.hashing';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Password::class => [self::class, 'createPassword'],
            'password' => function (ContainerInterface $container) {
                return $container->get(Password::class);
            },
            'hashing.options' => [self::class, 'createOptions'],
        ];
    }

    public static function createPassword(ContainerInterface $container): Password
    {
        if ($container->has(ConfigManager::class)) {
            $config = $container->get(ConfigManager::class)->get('hashing');
        } else {
            $config = self::get($container, 'hashing.options');
        }

        $encrypt = new Password(
            $config['key']
        );

        return $encrypt;
    }

    public static function createOptions(ContainerInterface $container) : array
    {
        return [
            'key' => self::get($container, 'key'),
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
        $namespacedName = self::PACKAGE . '.' . $name;

        return $container->has($namespacedName) ? $container->get($namespacedName) :
            ($container->has($name) ? $container->get($name) : $default);
    }
}
