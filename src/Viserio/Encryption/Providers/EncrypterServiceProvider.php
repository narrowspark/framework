<?php
declare(strict_types=1);
namespace Viserio\Encryption\Providers;

use Defuse\Crypto\Key;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Encryption\Encrypter;

class EncrypterServiceProvider implements ServiceProvider
{
    const PACKAGE = 'viserio.encryption';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Encrypter::class => [self::class, 'createEncrypter'],
            EncrypterContract::class => function (ContainerInterface $container) {
                return $container->get(Encrypter::class);
            },
            'encrypter' => function (ContainerInterface $container) {
                return $container->get(Encrypter::class);
            },
            'encrypter.options' => [self::class, 'createOptions'],
        ];
    }

    public static function createEncrypter(ContainerInterface $container): Encrypter
    {
        if ($container->has(ConfigManager::class)) {
            $config = $container->get(ConfigManager::class)->get('encrypter');
        } else {
            $config = self::get($container, 'encrypter.options');
        }

        $encrypt = new Encrypter(
            Key::loadFromAsciiSafeString(
                $config['key']
            )
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
