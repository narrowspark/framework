<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Traits;

use Psr\Container\ContainerInterface;
use Viserio\Component\OptionsResolver\OptionsResolver;

trait ConfigurationTrait
{
    /**
     * Config array.
     *
     * @var \ArrayAccess|array
     */
    protected $options;

    /**
     * Cache resolver class.
     *
     * @var \Viserio\Component\OptionsResolver\OptionsResolver
     */
    protected $resolvedClass;

    /**
     * Configure and resolve component options.
     *
     * @param \Psr\Container\ContainerInterface|iterable $data
     * @param string|null                                $id
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

            if ($container !== null && $container->has(OptionsResolver::class)) {
                $this->resolvedClass = $container->get(OptionsResolver::class);
            } else {
                $this->resolvedClass = new OptionsResolver();
            }
        }

        $this->options = $this->resolvedClass->configure($this, $data)->resolve($id);
    }
}
