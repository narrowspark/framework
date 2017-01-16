<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Support\Traits;

use Interop\Container\ContainerInterface;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;

trait ServiceProviderConfigAwareTrait
{
    /**
     * Get the config from config manager or container.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param string                                $id
     * @param mixed                                 $default
     *
     * @return mixed
     */
    protected static function getConfig(ContainerInterface $container, string $id = '', $default = null)
    {
        $configName = str_replace('viserio.', '', self::PACKAGE);
        $isPrefix   = $configName === $id;

        if ($container->has(RepositoryContract::class)) {
            $id = ($isPrefix ? $configName : $configName . '.' . $id);

            return $container->get(RepositoryContract::class)->get($id, $default);
        }

        if ($isPrefix) {
            return self::get($container, 'options');
        }

        return self::getDotedConfig(self::get($container, 'options'), $id, $default);
    }

    /**
     * Returns the entry named PACKAGE.$name, of simply $name if PACKAGE.$name is not found.
     *
     * @param ContainerInterface $container
     * @param string             $name
     *
     * @return mixed
     */
    private static function get(ContainerInterface $container, string $name)
    {
        $namespacedName = self::PACKAGE . '.' . $name;

        return $container->has($namespacedName) ? $container->get($namespacedName) :
            ($container->has($name) ? $container->get($name) : []);
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param array  $array
     * @param string $id
     * @param mixed  $default
     *
     * @return mixed
     */
    private static function getDotedConfig(array $array, string $id, $default)
    {
        foreach (explode('.', $id) as $segment) {
            if (! array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }
}
