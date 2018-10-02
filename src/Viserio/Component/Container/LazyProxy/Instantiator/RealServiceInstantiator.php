<?php
declare(strict_types=1);
namespace Viserio\Component\Container\LazyProxy\Instantiator;

use Closure;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\Container\Compiler\Definition as DefinitionContract;
use Viserio\Component\Contract\Container\LazyProxy\Instantiator as InstantiatorContract;

class RealServiceInstantiator implements InstantiatorContract
{
    /**
     * {@inheritdoc}
     */
    public function instantiateProxy(
        ContainerInterface $container,
        DefinitionContract $definition,
        Closure $realInstantiator
    ): object {
        return $realInstantiator();
    }
}
