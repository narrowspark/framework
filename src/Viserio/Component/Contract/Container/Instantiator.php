<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Container;

use Closure;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\Container\Compiler\Definition as DefinitionContract;

interface Instantiator
{
    /**
     * Instantiates a proxy object.
     *
     * @param \Psr\Container\ContainerInterface                         $container        The container from which the service is being requested
     * @param \Viserio\Component\Contract\Container\Compiler\Definition $definition       The definition of the requested service
     * @param \Closure                                                  $realInstantiator Zero-argument callback that is capable of producing the real service instance
     *
     * @return object
     */
    public function instantiateProxy(
        ContainerInterface $container,
        DefinitionContract $definition,
        Closure $realInstantiator
    ): object;
}
