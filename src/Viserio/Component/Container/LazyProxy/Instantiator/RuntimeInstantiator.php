<?php
declare(strict_types=1);
namespace Viserio\Component\Container\LazyProxy\Instantiator;

use Closure;
use ProxyManager\Configuration;
use ProxyManager\GeneratorStrategy\EvaluatingGeneratorStrategy;
use ProxyManager\Proxy\LazyLoadingInterface;
use Psr\Container\ContainerInterface;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Viserio\Component\Container\LazyProxy\LazyLoadingValueHolderFactory;
use Viserio\Component\Container\Tests\Fixture\Proxy\ClassToProxy;
use Viserio\Component\Contract\Container\Compiler\Definition as DefinitionContract;
use Viserio\Component\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Container\Exception\RuntimeException;
use Viserio\Component\Contract\Container\LazyProxy\Instantiator as InstantiatorContract;

class RuntimeInstantiator implements InstantiatorContract
{
    /**
     * A LazyLoadingValueHolderFactory instance.
     *
     * @var null|\Viserio\Component\Container\LazyProxy\LazyLoadingValueHolderFactory
     */
    private $proxyManager;

    /**
     * Create a new RuntimeInstantiator instance.
     *
     * @return void
     */
    public function __construct()
    {
        if (! \class_exists(Configuration::class)) {
            throw new RuntimeException('The ocramius/proxy-manager library is not installed. Lazy injection requires that library to be installed with Composer in order to work. Run "composer require ocramius/proxy-manager:^2.2".');
        }

        $config = new Configuration();
        $config->setGeneratorStrategy(new EvaluatingGeneratorStrategy());

        $this->proxyManager = new LazyLoadingValueHolderFactory($config);
    }

    /**
     * {@inheritdoc}
     */
    public function instantiateProxy(
        ContainerInterface $container,
        DefinitionContract $definition,
        Closure $realInstantiator
    ): object {
        self::verifyClassCanBeProxied($definition->getReflector());

        return $this->proxyManager->createProxy(
            ClassToProxy::class,
            function (&$wrappedObject, LazyLoadingInterface $proxy, $method, $params, &$initializer) use ($realInstantiator) {
                $wrappedObject = $realInstantiator();
                $initializer = null; // turning off further lazy initialization

                return true;
            }
        );
    }

    /**
     * Check if class can be a proxy.
     *
     * @param \Roave\BetterReflection\Reflection\ReflectionClass $reflectionClass
     *
     * @throws \Viserio\Component\Contract\Container\Exception\InvalidArgumentException
     *
     * @return void
     */
    private static function verifyClassCanBeProxied(ReflectionClass $reflectionClass): void
    {
        if ($reflectionClass->isFinal()) {
            throw InvalidArgumentException::classMustNotBeFinal($reflectionClass->getName());
        }

        if ($reflectionClass->isAbstract()) {
            throw InvalidArgumentException::classMustNotBeAbstract($reflectionClass->getName());
        }
    }
}
