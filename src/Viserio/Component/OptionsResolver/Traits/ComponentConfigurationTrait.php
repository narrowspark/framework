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
    protected $resolvedClass;

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
        if ($this->resolvedClass === null) {
            $container = null;

            if ($data instanceof ContainerInterface) {
                $container = $data;
            } elseif (isset($this->container) && $this->container instanceof ContainerInterface) {
                $container = $this->container;
            }

            if ($container !== null && $container->has(ComponentOptionsResolver::class)) {
                $this->resolvedClass = $container->get(ComponentOptionsResolver::class);
            } else {
                $this->resolvedClass = new ComponentOptionsResolver();
            }
        }

        $this->options = $this->resolvedClass->configure($this, $data)->resolve($id);
    }
}
