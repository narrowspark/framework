<?php
declare(strict_types=1);
namespace Viserio\Contracts\Container\Traits;

use Interop\Container\ContainerInterface;
use RuntimeException;

trait ContainerAwareTrait
{
    /**
     * Container instance.
     *
     * @var \Interop\Container\ContainerInterface|null
     */
    protected $container;

    /**
     * Set a container instance.
     *
     * @param \Interop\Container\ContainerInterface $container
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
     * @return \Interop\Container\ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        if (! $this->container) {
            throw new RuntimeException('Container is not set up.');
        }

        return $this->container;
    }
}
