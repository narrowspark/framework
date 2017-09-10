<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Container\Traits;

use Psr\Container\ContainerInterface;

trait ContainerAwareTrait
{
    /**
     * Container instance.
     *
     * @var null|\Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * Set a container instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }
}
