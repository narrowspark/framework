<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Container;

use Closure;
use Invoker\Invoker;
use Invoker\InvokerInterface;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\ParameterNameContainerResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ResolverChain;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use Symfony\Contracts\Service\ResetInterface;
use Viserio\Component\Container\Definition\FactoryDefinition;
use Viserio\Component\Container\Traits\ReflectorHelpersTrait;
use Viserio\Component\Container\Traits\ReflectorTrait;
use Viserio\Component\Container\Traits\TypeNotFoundMessageCreatorTrait;
use Viserio\Contract\Container\CompiledContainer as CompiledContainerContract;
use Viserio\Contract\Container\DelegateAwareContainer as DelegateAwareContainerContract;
use Viserio\Contract\Container\Exception\BindingResolutionException;
use Viserio\Contract\Container\Exception\CircularDependencyException;
use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Contract\Container\Exception\NotFoundException;
use Viserio\Contract\Container\Exception\UnresolvableDependencyException;
use Viserio\Contract\Support\Resettable as ResettableContract;

abstract class AbstractCompiledContainer implements CompiledContainerContract, DelegateAwareContainerContract
{
    use ReflectorTrait {
        getClassReflector as protectedGetClassReflector;
    }
    use ReflectorHelpersTrait;
    use TypeNotFoundMessageCreatorTrait;

    /**
     * The stack of concretions currently being built.
     *
     * @var array
     */
    protected $compiledBuildStack = [];

    /**
     * The container's shared services.
     *
     * @var array
     */
    protected $services = [];

    /**
     * Private services are directly used in the compiled method.
     *
     * @var array
     */
    protected $privates = [];

    /**
     * List of mapped id names to method names.
     *
     * @var array
     */
    protected $methodMapping = [];

    /**
     * List of synthetic ids.
     *
     * @var array
     */
    protected $syntheticIds = [];

    /**
     * List of uninitialized references.
     *
     * @var array
     */
    protected $uninitializedServices = [];

    /**
     * List of generated files.
     *
     * @var array
     */
    protected $fileMap = [];

    /**
     * The collection of parameters.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * The registered type aliases.
     *
     * @var string[]
     */
    protected $aliases = [];

    /** @var null|\Invoker\InvokerInterface */
    protected $invoker;

    /**
     * Array full of container implementing the ContainerInterface.
     *
     * @var \Psr\Container\ContainerInterface[]
     */
    protected $delegates = [];

    /**
     * Clone method.
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    private function __clone()
    {
    }

    /**
     * Configured invoker.
     *
     * @return \Invoker\InvokerInterface
     */
    private function getInvoker(): InvokerInterface
    {
        if (! $this->invoker) {
            $parameterResolver = new ResolverChain([
                new NumericArrayResolver(),
                new AssociativeArrayResolver(),
                new DefaultValueResolver(),
                new TypeHintContainerResolver($this),
                new ParameterNameContainerResolver($this),
            ]);
            $this->invoker = new Invoker($parameterResolver, $this);
        }

        return $this->invoker;
    }

    /**
     * {@inheritdoc}
     */
    public function setDelegates(array $delegates): self
    {
        $this->delegates = $delegates;

        return $this;
    }

    /**
     * Gets service ids that existed at compile time.
     *
     * @return array
     */
    public function getRemovedIds(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $id, $service): void
    {
        if ($id === ContainerInterface::class) {
            throw new InvalidArgumentException(\sprintf('You cannot set service [%s].', ContainerInterface::class));
        }

        if (! (\array_key_exists($id, $this->fileMap) || \array_key_exists($id, $this->methodMapping))) {
            if (\array_key_exists($id, $this->syntheticIds) || ! \array_key_exists($id, $this->getRemovedIds())) {
                // no-op
            } elseif ($service === null) {
                throw new InvalidArgumentException(\sprintf('The [%s] service is private, you cannot unset it.', $id));
            } else {
                throw new InvalidArgumentException(\sprintf('The [%s] service is private, you cannot replace it.', $id));
            }
        } elseif (\array_key_exists($id, $this->services)) {
            throw new InvalidArgumentException(\sprintf('The [%s] service is already initialized, you cannot replace it.', $id));
        }

        if (\array_key_exists($id, $this->aliases)) {
            unset($this->aliases[$id]);
        }

        if ($service === null) {
            unset($this->services[$id]);

            return;
        }

        unset($this->uninitializedServices[$id]);

        $this->services[$id] = $service;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        // optimized code, this should really look like this.
        return $this->services[$id]
            ?? $this->services[$id = $this->aliases[$id] ?? $id]
            ?? ($id === ContainerInterface::class ? $this : ([$this, 'doGet'])($id));
    }

    /**
     * {@inheritdoc}
     */
    public function has($id): bool
    {
        if (\array_key_exists($id, $this->aliases)) {
            $id = $this->aliases[$id];
        }

        return $id === ContainerInterface::class || \array_key_exists($id, $this->services) || \array_key_exists($id, $this->methodMapping) || \array_key_exists($id, $this->syntheticIds) || \array_key_exists($id, $this->fileMap) || \array_key_exists($id, $this->parameters) || $this->hasInDelegate($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameter(string $name)
    {
        if (! \array_key_exists($name, $this->parameters)) {
            if ($name === '') {
                throw new InvalidArgumentException('You called getParameter with a empty argument.');
            }

            $alternatives = [];

            foreach ($this->parameters as $key => $parameterValue) {
                $lev = \levenshtein($name, $key);

                if ($lev <= \strlen($name) / 3 || false !== \strpos($key, $name)) {
                    $alternatives[] = $key;
                }
            }

            $nonNestedAlternative = null;

            if (\count($alternatives) === 0 && \strpos($name, '.') !== false) {
                $namePartsLength = \array_map('\strlen', \explode('.', $name));
                $key = \substr($name, 0, (int) (-1 * (1 + \array_pop($namePartsLength))));

                while (\count($namePartsLength)) {
                    if ($this->hasParameter($key)) {
                        if (\is_array($this->getParameter($key))) {
                            $nonNestedAlternative = $key;
                        }

                        break;
                    }

                    $key = \substr($key, 0, (int) (-1 * (1 + \array_pop($namePartsLength))));
                }
            }

            $message = \sprintf('You have requested a non-existent parameter [%s].', $name);

            if ($nonNestedAlternative !== null) {
                $message .= ' You cannot access nested array items, do you want to inject [' . $nonNestedAlternative . '] instead?';
            }

            throw new NotFoundException($name, null, null, $alternatives, $message);
        }

        return $this->parameters[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function hasParameter(string $id): bool
    {
        return \array_key_exists($id, $this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function delegate(ContainerInterface $container): self
    {
        $this->delegates[] = $container;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasInDelegate(string $abstract): bool
    {
        foreach ($this->delegates as $container) {
            if ($container->has($abstract)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $services = \array_merge($this->services, $this->privates);
        $this->services = $this->delegates = $this->privates = $this->parameters = [];

        foreach ($services as $service) {
            try {
                if ($service instanceof ResettableContract || $service instanceof ResetInterface) {
                    $service->reset();
                }
            } catch (\Throwable $e) {
                continue;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function make($abstract, array $arguments = [], bool $shared = true)
    {
        if (ContainerInterface::class === $abstract) {
            return $this;
        }

        $className = $abstract;

        if (\is_object($abstract)) {
            $className = \get_class($abstract);
            $hash = ContainerBuilder::getHash($className);
        } else {
            $hash = $className = $this->aliases[$className] ?? $className;
        }

        // If an instance of the type is currently being managed as a singleton we'll
        // just return an existing instance instead of instantiating new instances
        // so the developer can keep using the same objects instance every time.
        if (\array_key_exists($hash, $this->services)) {
            return $this->services[$hash];
        }

        if ($this->ambiguousServiceTypes === null) {
            $this->populateAvailableTypes();
        }

        if (! $abstract instanceof Closure && (\is_object($abstract) || is_class($className))) {
            $reflectionClass = $this->getClassReflector($className);

            if (\in_array($abstract, $this->compiledBuildStack, true)) {
                $this->compiledBuildStack[] = $abstract;

                throw new CircularDependencyException($abstract, $this->compiledBuildStack);
            }

            if ($reflectionClass === null) {
                throw new BindingResolutionException(\sprintf('The class [%s] does not exist.', $className));
            }

            $this->compiledBuildStack[] = $reflectionClass->getName();

            $constructor = $this->getConstructor($reflectionClass, \sprintf('Constructor of [%s] must be public.', $className));

            if ($constructor !== null) {
                $arguments = $this->autowireMethod($constructor, $arguments, $className);
            }

            \array_pop($this->compiledBuildStack);

            return $shared ? $this->services[$hash] = $reflectionClass->newInstanceArgs($arguments) : $reflectionClass->newInstanceArgs($arguments);
        }

        if (is_function($abstract)) {
            $functionReflector = $this->getFunctionReflector($abstract);

            $this->compiledBuildStack[] = $functionReflector->getName();

            $resolvedArguments = $this->autowireMethod($functionReflector, $arguments, \gettype($abstract));

            \array_pop($this->compiledBuildStack);

            return $shared ? $this->services[$hash] = $functionReflector->invokeArgs($resolvedArguments) : $functionReflector->invokeArgs($resolvedArguments);
        }

        if (is_method($abstract) || \is_callable($abstract) || (\is_array($abstract) && isset($abstract[1]) && $abstract[1] === '__invoke')) {
            [$class, $method] = FactoryDefinition::splitFactory($abstract);

            $reflectionClass = $this->getClassReflector($class);
            /** @var \ReflectionMethod $reflectionMethod */
            $reflectionMethod = $this->getMethodReflector($reflectionClass, $method);

            $this->compiledBuildStack[] = $reflectionMethod->getName();

            $resolvedArguments = $this->autowireMethod($reflectionMethod, $arguments, \is_object($abstract) ? $className : \gettype($abstract));

            \array_pop($this->compiledBuildStack);

            return $shared ? $this->services[$hash] = $reflectionMethod->invokeArgs($reflectionClass->newInstanceWithoutConstructor(), $resolvedArguments) : $reflectionMethod->invokeArgs($reflectionClass->newInstanceWithoutConstructor(), $resolvedArguments);
        }

        throw new BindingResolutionException(\sprintf('[%s] is not resolvable. Build stack : [%s]', \is_object($abstract) ? $className : \gettype($abstract), \implode(', ', $this->compiledBuildStack)));
    }

    /**
     * Call the given function using the given parameters.
     *
     * @param callable|string $callable   function to call
     * @param array           $parameters parameters to use
     *
     * @throws \Invoker\Exception\InvocationException          base exception class for all the sub-exceptions below
     * @throws \Invoker\Exception\NotCallableException
     * @throws \Invoker\Exception\NotEnoughParametersException
     *
     * @return mixed result of the function
     */
    public function call($callable, array $parameters = [])
    {
        if (is_method($callable)) {
            $callable = FactoryDefinition::splitFactory($callable);
        }

        return $this->getInvoker()->call($callable, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    protected function getClassReflector(string $abstract, bool $throw = true): ?ReflectionClass
    {
        $reflectionClass = $this->protectedGetClassReflector($abstract, $throw);

        if ($reflectionClass !== null && ! $reflectionClass->isInstantiable()) {
            throw new BindingResolutionException(\sprintf('The %s [%s] is not instantiable. Build stack: [%s].', $reflectionClass->isInterface() ? 'interface' : 'class', $abstract, \implode('-> ', $this->compiledBuildStack)));
        }

        return $reflectionClass;
    }

    /**
     * Attempt to get a service from the stack of delegated backup containers.
     *
     * @param string $abstract
     *
     * @return mixed
     */
    protected function getFromDelegate(string $abstract)
    {
        foreach ($this->delegates as $container) {
            if ($container->has($abstract)) {
                return $container->get($abstract);
            }
        }

        return null;
    }

    /**
     * As a separate method to allow "get()" to use the really fast `??` operator.
     *
     * @param string $id
     *
     *@throws \Viserio\Contract\Container\Exception\NotFoundException
     * @throws \Viserio\Contract\Container\Exception\CircularDependencyException
     * @throws \Throwable
     *
     * @return mixed
     */
    protected function doGet(string $id)
    {
        if (\array_key_exists($id, $this->compiledBuildStack)) {
            $this->compiledBuildStack[$id] = true;

            throw new CircularDependencyException($id, $this->compiledBuildStack);
        }

        $this->compiledBuildStack[$id] = true;

        try {
            $uninitializedServices = \array_key_exists($id, $this->uninitializedServices);

            if (\array_key_exists($id, $this->fileMap)) {
                return $uninitializedServices ? null : $this->load($this->fileMap[$id]);
            }

            // If it's a compiled entry, then there is a method in this class
            if (\array_key_exists($id, $this->methodMapping)) {
                return $uninitializedServices ? null : $this->{$this->methodMapping[$id]}();
            }
        } catch (\Throwable $exception) {
            unset($this->services[$id]);

            throw $exception;
        } finally {
            \array_pop($this->compiledBuildStack);
        }

        if (($resolved = $this->getFromDelegate($id)) !== null) {
            return $resolved;
        }

        if (\array_key_exists($id, $this->syntheticIds) && \array_key_exists($id, $this->uninitializedServices)) {
            return null;
        }

        if (\array_key_exists($id, $this->syntheticIds)) {
            throw new NotFoundException($id, null, null, [], \sprintf('The [%s] service is synthetic, it needs to be set at boot time before it can be used.', $id));
        }

        if (\array_key_exists($id, $this->getRemovedIds())) {
            $message = \sprintf('The [%s] service or alias has been removed or inlined when the container was compiled. You should either make it public, or stop using the container directly and use dependency injection instead.', $id);

            throw new NotFoundException($id, null, null, [], $message);
        }
        // @todo show if the alternative is a parameter or binding
        $alternatives = [];
        $ids = \array_unique(\array_merge(\array_keys($this->parameters), \array_keys($this->methodMapping), \array_keys($this->fileMap)));

        foreach ($ids as $knownId) {
            if ('' === $knownId || '.' === $knownId[0]) {
                continue;
            }

            $lev = \levenshtein($id, $knownId);

            if ($lev <= \strlen($id) / 3 || false !== \strpos($knownId, $id)) {
                $alternatives[] = $knownId;
            }
        }

        throw new NotFoundException($id, null, null, $alternatives);
    }

    /**
     * Creates a service by requiring its factory file.
     *
     * @codeCoverageIgnore
     *
     * @param string $file
     *
     * @return object The service created by the file
     */
    protected function load(string $file): object
    {
        return require $file;
    }

    /**
     * Autowires the constructor or a method.
     *
     * @param \ReflectionFunctionAbstract $reflectionMethod
     * @param array                       $arguments
     * @param null|string                 $currentId
     *
     * @throws \Viserio\Contract\Container\Exception\UnresolvableDependencyException
     * @throws \ReflectionException
     *
     * @return array The autowired parameters
     */
    private function autowireMethod(
        ReflectionFunctionAbstract $reflectionMethod,
        array $arguments,
        string $currentId
    ): array {
        $class = $reflectionMethod instanceof ReflectionMethod ? $reflectionMethod->getDeclaringClass()->getName() : '';
        $method = $reflectionMethod->getName();

        /** @var \ReflectionParameter[] $parameters */
        $parameters = $reflectionMethod->getParameters();

        if ($reflectionMethod->isVariadic()) {
            \array_pop($parameters);
        }

        foreach ($parameters as $index => $parameter) {
            if (\array_key_exists($index, $arguments) && $arguments[$index] !== '') {
                continue;
            }

            $type = $this->getTypeHint($reflectionMethod, $parameter, true);

            if ($type === null) {
                if (\array_key_exists($index, $arguments)) {
                    continue;
                }

                // no default value? Then fail
                if (! $parameter->isDefaultValueAvailable()) {
                    // For core classes, isDefaultValueAvailable() can
                    // be false when isOptional() returns true. If the
                    // parameter *is* optional, allow it to be missing
                    if ($parameter->isOptional()) {
                        continue;
                    }

                    $type = $this->getTypeHint($reflectionMethod, $parameter);
                    $type = $type ? \sprintf('is type-hinted [%s]', \ltrim($type, '\\')) : 'has no type-hint';

                    throw new UnresolvableDependencyException($currentId, \sprintf('Argument [$%s] of method [%s] %s, you should configure its value explicitly.', $parameter->getName(), $class . '::' . $method, $type));
                }

                // specifically pass the default value
                $arguments[$index] = $parameter->getDefaultValue();

                continue;
            }

            $getValue = function () use ($type, $parameter, $class, $method, $currentId) {
                if (\interface_exists($type)) {
                    $value = $this->has($type) ? $this->get($type) : null;
                } else {
                    $value = $this->make($type);
                }

                if ($value === null) {
                    if ($parameter->isDefaultValueAvailable()) {
                        $value = $parameter->getDefaultValue();
                    } elseif (! $parameter->allowsNull()) {
                        throw new UnresolvableDependencyException($currentId, $this->createTypeNotFoundMessage($type, \sprintf('argument [$%s] of method [%s]', $parameter->getName(), $currentId !== $class ? $class . '::' . $method : $method), $currentId));
                    }
                }

                return $value;
            };

            $arguments[$index] = $getValue();
        }

        $index = 0;

        if (! \array_key_exists(++$index, $arguments) && \count($parameters) !== 0) {
            while (0 <= --$index) {
                $parameter = $parameters[$index];

                if (! $parameter->isDefaultValueAvailable() || $parameter->getDefaultValue() !== $arguments[$index]) {
                    break;
                }

                unset($arguments[$index]);
            }
        }

        // it's possible index 1 was set, then index 0, then 2, etc
        // make sure that we re-order so they're injected as expected
        \ksort($arguments);

        return $arguments;
    }

    /**
     * {@inheritdoc}
     */
    protected function getServicesAndAliases(): array
    {
        return \array_unique(
            \array_merge(
                \array_keys($this->aliases),
                \array_keys($this->methodMapping),
                \array_keys($this->fileMap)
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function populateAvailableTypes(): void
    {
        $this->types = [];
        $this->ambiguousServiceTypes = [];

        foreach ($this->methodMapping as $id => $method) {
            $value = $this->get($id);

            if (! \is_object($value) || $value instanceof Closure) {
                return;
            }

            $this->populateAvailableType($id, \get_class($value));
        }
    }
}
