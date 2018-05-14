<?php
declare(strict_types=1);
namespace Viserio\Component\Container\LazyProxy;

use ProxyManager\Factory\LazyLoadingValueHolderFactory as BaseFactory;
use ProxyManager\ProxyGenerator\ProxyGeneratorInterface;

/**
 * @internal
 */
final class LazyLoadingValueHolderFactory extends BaseFactory
{
    /**
     * @var null|\ProxyManager\ProxyGenerator\LazyLoadingValueHolderGenerator|\Viserio\Component\Container\LazyProxy\LazyLoadingValueHolderGenerator
     */
    private $generator;

    /**
     * {@inheritdoc}
     */
    protected function getGenerator(): ProxyGeneratorInterface
    {
        return $this->generator ?: $this->generator = new LazyLoadingValueHolderGenerator();
    }
}
