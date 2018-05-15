<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Definition;

use Psr\Container\ContainerInterface;

abstract class AbstractSelfResolvingDefinition extends AbstractDefinition
{
    /**
     * Resolve the definition and return the resulting value.
     *
     * @var \Psr\Container\ContainerInterface
     *
     * @return mixed
     */
    abstract public function resolve(ContainerInterface $container);

    /**
     * Check if a definition can be resolved.
     *
     * @var \Psr\Container\ContainerInterface
     *
     * @return bool
     */
    abstract public function isResolvable(ContainerInterface $container) : bool;
}
