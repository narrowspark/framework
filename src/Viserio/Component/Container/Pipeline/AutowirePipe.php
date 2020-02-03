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

namespace Viserio\Component\Container\Pipeline;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionParameter;
use Viserio\Component\Container\ClassHelper;
use Viserio\Component\Container\Definition\ObjectDefinition;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Traits\ReflectorHelpersTrait;
use Viserio\Component\Container\Traits\TypeNotFoundMessageCreatorTrait;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\AutowiredAwareDefinition as AutowiredAwareDefinitionContract;
use Viserio\Contract\Container\Definition\ClosureDefinition as ClosureDefinitionContract;
use Viserio\Contract\Container\Definition\FactoryDefinition as FactoryDefinitionContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;
use Viserio\Contract\Container\Exception\BindingResolutionException;
use Viserio\Contract\Container\Exception\RuntimeException;
use Viserio\Contract\Container\Exception\UnresolvableDependencyException;

/**
 * @internal
 */
final class AutowirePipe extends AbstractRecursivePipe
{
    use ReflectorHelpersTrait;
    use TypeNotFoundMessageCreatorTrait;

    /** @var bool */
    private $throw = false;

    /** @var null|array */
    private $methodCalls;

    /** @var null|string */
    private $decoratedClass;

    /** @var null|string */
    private $decoratedId;

    /** @var null|int */
    private $decoratedMethodIndex;

    /** @var null|int */
    private $decoratedMethodArgumentIndex;

    /** @var null|callable */
    private $getPreviousValue;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        $this->containerBuilder = $containerBuilder;

        if ($this->ambiguousServiceTypes === null) {
            $this->populateAvailableTypes();
        }

        try {
            parent::process($containerBuilder);
        } finally {
            $this->types = null;
            $this->ambiguousServiceTypes = null;
            $this->methodCalls = null;

            $this->decoratedClass = null;
            $this->decoratedId = null;
            $this->getPreviousValue = null;
            $this->decoratedMethodIndex = null;
            $this->decoratedMethodArgumentIndex = null;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function processValue($value, bool $isRoot = false)
    {
        if ($value instanceof ReferenceDefinitionContract && $ref = $this->getAutowiredReference($value)) {
            return $ref;
        }

        if ($value instanceof AutowiredAwareDefinitionContract && ! $value->isAutowired()) {
            return $value;
        }

        $value = parent::processValue($value, $isRoot);

        if ($value instanceof ClosureDefinitionContract) {
            $value->setArguments($this->autowireMethod($this->containerBuilder->getFunctionReflector($value->getValue()), []));

            return $value;
        }

        if (! $value instanceof ObjectDefinitionContract && ! $value instanceof FactoryDefinitionContract) {
            return $value;
        }

        if ($value->isSynthetic()) {
            return $value;
        }

        $class = $value->getClass();

        if ($value instanceof FactoryDefinitionContract && $class === ReferenceDefinition::class) {
            [$ref,] = $value->getValue();

            return $this->processValue($this->containerBuilder->findDefinition($ref->getName()), $isRoot);
        }

        try {
            $reflectionClass = $this->containerBuilder->getClassReflector($class);

            if ($reflectionClass === null) {
                throw new RuntimeException(\sprintf('Invalid service [%s]: class [%s] does not exist.', $this->currentId, $class));
            }
        } catch (ReflectionException $e) {
            throw new RuntimeException(\sprintf('Invalid service [%s]: %s.', $this->currentId, \lcfirst(\rtrim($e->getMessage(), '.'))));
        }

        if ($this->throw && ! $reflectionClass->isInstantiable()) {
            throw new BindingResolutionException(\sprintf('Invalid service [%s]: The class [%s] is not instantiable.', $this->currentId, $class));
        }

        $this->methodCalls = [];

        $constructor = $this->getConstructor($reflectionClass, \sprintf('Invalid service [%s]: %s must be public.', $this->currentId, \sprintf($class !== $this->currentId ? 'constructor of class [%s]' : 'its constructor', $class)));
        $hasConstructor = $constructor !== null && \strpos($class, 'class@anonymous') === false;
        $arguments = null;

        if ($hasConstructor) {
            $arguments = $value instanceof FactoryDefinitionContract ? $value->getClassArguments() : $value->getArguments();

            $this->methodCalls[] = [$constructor, $arguments];
        }

        if ($value instanceof ObjectDefinitionContract && $value->getChange('method_calls')) {
            $this->methodCalls = \array_merge($this->methodCalls, $value->getMethodCalls());
        } elseif ($value instanceof FactoryDefinitionContract) {
            /** @var ReflectionMethod $reflectionMethod */
            $reflectionMethod = $this->containerBuilder->getMethodReflector($reflectionClass, $value->getMethod());

            $value->setStatic($reflectionMethod->isStatic());

            $this->methodCalls[] = [$reflectionMethod, $value->getArguments()];
        }

        $this->autowireCalls($reflectionClass, $isRoot);

        if ($hasConstructor && $arguments !== null) {
            [, $autowireArguments] = \array_shift($this->methodCalls);

            if (\is_array($autowireArguments) && $autowireArguments !== $arguments) {
                $value instanceof FactoryDefinitionContract
                    ? $value->setClassArguments($autowireArguments)
                    : $value->setArguments($autowireArguments);
            }
        }

        if ($value instanceof ObjectDefinitionContract) {
            if ($this->methodCalls !== $value->getMethodCalls()) {
                $value->setMethodCalls($this->methodCalls);
            }

            return $value;
        }

        [, $autowireArguments] = \array_shift($this->methodCalls);

        if (\is_array($autowireArguments) && $autowireArguments !== $value->getArguments()) {
            $value->setArguments($autowireArguments ?? []);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    protected function has(string $id): bool
    {
        return $this->containerBuilder->has($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function populateAvailableTypes(): void
    {
        $this->types = [];
        $this->ambiguousServiceTypes = [];

        foreach ($this->containerBuilder->getDefinitions() as $id => $definition) {
            if (! $definition instanceof ObjectDefinitionContract || $definition->isDeprecated()) {
                return;
            }

            $this->populateAvailableType($id, $definition->getClass());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getClassReflector(string $class, bool $throw = true): ?ReflectionClass
    {
        return $this->containerBuilder->getClassReflector($class, $throw);
    }

    /**
     * {@inheritdoc}
     */
    protected function getServicesAndAliases(): array
    {
        return \array_unique(
            \array_merge(
                \array_keys($this->containerBuilder->getDefinitions()),
                \array_keys($this->containerBuilder->getAliases())
            )
        );
    }

    /**
     * Resolve methods calls.
     *
     * @param ReflectionClass $reflectionClass
     * @param bool            $isRoot
     *
     * @throws ReflectionException
     * @throws \Viserio\Contract\Container\Exception\UnresolvableDependencyException
     * @throws \Viserio\Contract\Container\Exception\CircularDependencyException
     * @throws \Viserio\Contract\Container\Exception\NotFoundException
     *
     * @return void
     */
    private function autowireCalls(ReflectionClass $reflectionClass, bool $isRoot): void
    {
        $this->decoratedId = null;
        $this->decoratedClass = null;
        $this->getPreviousValue = null;

        if ($isRoot) {
            $definition = $this->containerBuilder->getDefinition($this->currentId);

            if ($definition->innerServiceId !== null && $this->containerBuilder->has($this->decoratedId = $definition->innerServiceId)) {
                /** @var FactoryDefinitionContract|ObjectDefinitionContract $foundDefinition */
                $foundDefinition = $this->containerBuilder->findDefinition($this->decoratedId);

                $this->decoratedClass = $foundDefinition->getClass();
            }
        }

        $calls = [];

        foreach ($this->methodCalls as $i => $call) {
            $this->decoratedMethodIndex = $i;

            [$method, $arguments] = $call;

            if ($method instanceof ReflectionFunctionAbstract) {
                $reflectionMethod = $method;
            } else {
                $reflectionMethod = $this->containerBuilder->getMethodReflector($reflectionClass, $method);
            }

            if (isset($call[2])) {
                $calls[$i] = [$method, $this->autowireMethod($reflectionMethod, $arguments), $call[2]];
            } else {
                $calls[$i] = [$method, $this->autowireMethod($reflectionMethod, $arguments)];
            }
        }

        $this->methodCalls = $calls;
    }

    /**
     * Autowires the constructor or a method.
     *
     * @param ReflectionFunctionAbstract $reflectionMethod
     * @param array                      $arguments
     *
     * @throws \Viserio\Contract\Container\Exception\UnresolvableDependencyException
     * @throws ReflectionException
     *
     * @return array The autowired parameters
     */
    private function autowireMethod(ReflectionFunctionAbstract $reflectionMethod, array $arguments): array
    {
        $class = $reflectionMethod instanceof ReflectionMethod ? $reflectionMethod->getDeclaringClass()->getName() : $this->currentId;
        $method = $reflectionMethod->getName();

        /** @var ReflectionParameter[] $parameters */
        $parameters = $reflectionMethod->getParameters();

        if ($reflectionMethod->isVariadic()) {
            \array_pop($parameters);
        }

        $index = 0;

        foreach ($parameters as $i => $parameter) {
            $index = $i;

            if (isset($arguments[$i]) && $arguments[$i] !== '') {
                continue;
            }

            $type = $this->getTypeHint($reflectionMethod, $parameter, true);

            if ($type === null) {
                if (isset($arguments[$i])) {
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

                    throw new UnresolvableDependencyException($this->currentId, \sprintf('Cannot autowire service [%s]: argument [$%s] of method [%s] %s, you should configure its value explicitly.', $this->currentId, $parameter->getName(), $class !== $this->currentId ? $class . '::' . $method : $method, $type));
                }

                // specifically pass the default value
                $arguments[$i] = $parameter->getDefaultValue();

                continue;
            }

            $getValue = function () use ($type, $parameter, $class, $method) {
                $value = $this->getAutowiredReference(
                    (new ReferenceDefinition($type))
                        ->setType($type)
                        ->setVariableName($parameter->getName())
                );

                if ($value === null) {
                    if ($parameter->isDefaultValueAvailable()) {
                        $value = $parameter->getDefaultValue();
                    } elseif (! $parameter->allowsNull()) {
                        throw new UnresolvableDependencyException($this->currentId, $this->createTypeNotFoundMessage($type, \sprintf('argument [$%s] of method [%s]', $parameter->getName(), $this->currentId !== $class ? $class . '::' . $method : $method), $this->currentId));
                    }
                }

                return $value;
            };

            if ($this->decoratedClass && \is_a($this->decoratedClass, $type, true)) {
                if ($this->getPreviousValue) {
                    // The inner service is injected only if there is only 1 argument matching the type of the decorated class
                    // across all arguments of all autowired methods.
                    // If a second matching argument is found, the default behavior is restored.
                    $getPreviousValue = $this->getPreviousValue;

                    $this->methodCalls[$this->decoratedMethodIndex][1][$this->decoratedMethodArgumentIndex] = $getPreviousValue();
                    $this->decoratedClass = null; // Prevent further checks
                } else {
                    $arguments[$i] = (new ReferenceDefinition($this->decoratedId))->setType($this->decoratedClass);
                    $this->getPreviousValue = $getValue;
                    $this->decoratedMethodArgumentIndex = $i;

                    continue;
                }
            }

            $arguments[$i] = $getValue();
        }

        if (! isset($arguments[++$index]) && \count($parameters) !== 0) {
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
     * Returns a service to the matching the given type, if any exist.
     *
     * @param \Viserio\Contract\Container\Definition\ReferenceDefinition $reference
     *
     * @throws \Viserio\Contract\Container\Exception\CircularDependencyException
     * @throws \Viserio\Contract\Container\Exception\NotFoundException
     *
     * @return null|\Viserio\Contract\Container\Definition\ReferenceDefinition
     */
    private function getAutowiredReference(ReferenceDefinitionContract $reference): ?ReferenceDefinitionContract
    {
        $refName = $reference->getName();
        $changedRefType = false;

        if (null === $refType = $reference->getType()) {
            $changedRefType = true;
            $refType = $refName;
        }

        $refVariableName = $refType === ContainerInterface::class ? '$this' : $reference->getVariableName();
        $methodCalls = $reference->getMethodCalls();
        $behavior = $reference->getBehavior();

        if ($this->has($refType)) {
            $ref = new ReferenceDefinition($this->containerBuilder->findDefinition($refType)->getName(), $behavior);
            $ref->setMethodCalls($methodCalls);

            if (! $changedRefType) {
                $ref->setType($refType);
            }

            if ($refVariableName !== null) {
                $ref->setVariableName($refVariableName);
            }

            return $ref;
        }

        if (\interface_exists($refType, false)) {
            return null;
        }

        // trying to create class reference
        // check if class can be auto created
        try {
            $isAutoCreatable = ClassHelper::isClassLoaded($refType);
        } catch (ReflectionException $exception) {
            $isAutoCreatable = false;
        }

        if ($isAutoCreatable && \count($this->ambiguousServiceTypes) === 0 && ! $this->containerBuilder->hasDefinition($refType)) {
            try {
                $typeDefinition = new ObjectDefinition($refName, $refType, 5 /* DefinitionContract::SINGLETON + DefinitionContract::PRIVATE */);
                $this->throw = true;

                $this->containerBuilder->setDefinition($refType, $typeDefinition);

                $this->processValue($typeDefinition);
            } catch (BindingResolutionException | UnresolvableDependencyException $exception) {
                if (\in_array($behavior, [4/* ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE */, 3/* ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE */, 2/* ReferenceDefinition::NULL_ON_INVALID_REFERENCE */], true)) {
                    $this->containerBuilder->removeDefinition($refType);

                    return null;
                }

                throw new RuntimeException(\sprintf('Invalid service [%s]: Auto creation for [%s] with type [%s] failed.', $this->currentId, $refName, $refType));
            }

            $this->containerBuilder->log($this, \sprintf('Auto created a new definition for [%s].', $refType));

            $reference = new ReferenceDefinition($refName, $behavior);
            $reference->setType($refType)
                ->setMethodCalls($methodCalls);

            if ($refVariableName !== null) {
                $reference->setVariableName($refVariableName);
            }

            return $reference;
        }

        return null;
    }
}
