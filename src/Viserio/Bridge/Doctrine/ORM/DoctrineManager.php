<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM;

use Interop\Container\ContainerInterface;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;

class DoctrineManager
{
    use ContainerAwareTrait;

    /**
     * Create a new manager registry instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
