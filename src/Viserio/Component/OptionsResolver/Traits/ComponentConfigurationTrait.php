<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Traits;

use Interop\Container\ContainerInterface;
use Viserio\Component\OptionsResolver\ComponentOptionsResolver;

trait ComponentConfigurationTrait
{
    /**
     * Config array.
     *
     * @var array|\ArrayAccess
     */
    protected $options;

    /**
     * Cache resolver class.
     *
     * @var \Viserio\Component\OptionsResolver\ComponentOptionsResolver
     */
    protected static $resolvedClass;

    /**
     * Configure and resolve component options.
     *
     * @param \Interop\Container\ContainerInterface|iterable $data
     * @param string|null                                    $id
     *
     * @return void
     */
    public function configureOptions($data, string $id = null): void
    {
        if (static::$resolvedClass === null) {
            $container = null;

            if ($data instanceof ContainerInterface) {
                $container = $data;
            } elseif (isset($this->container)) {
                $container = $this->container instanceof ContainerInterface ? $this->container : null;
            }

            if ($container !== null && $container->has(ComponentOptionsResolver::class)) {
                static::$resolvedClass = $container->get(ComponentOptionsResolver::class);
            } else {
                static::$resolvedClass = new ComponentOptionsResolver();
            }
        }

        $this->options = static::$resolvedClass->configure($this, $data)->resolve($id);
    }
}
