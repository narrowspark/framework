<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Container\Traits;

use Psr\Container\ContainerInterface;
use RuntimeException;

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

    /**
     * Get the container instance.
     *
     * @throws \RuntimeException
     *
     * @return \Psr\Container\ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        if (! $this->container) {
            throw new RuntimeException('Container is not set up.');
        }

        return $this->container;
    }
}
