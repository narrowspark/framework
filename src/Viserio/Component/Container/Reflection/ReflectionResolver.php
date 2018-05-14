<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Reflection;

use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionFunction;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use RuntimeException as BaseRuntimeException;
use Viserio\Component\Contract\Container\Exception\BindingResolutionException;
use Viserio\Component\Contract\Container\Exception\CyclicDependencyException;

abstract class ReflectionResolver
{
    /**
     * The stack of concretions currently being built.
     *
     * @var array
     */
    protected $buildStack = [];

    /**
     * Resolve a bound class.
     *
     * @param \Roave\BetterReflection\Reflection\ReflectionClass                              $reflectionClass
     * @param \ReflectionParameter[]|\Roave\BetterReflection\Reflection\ReflectionParameter[] $reflectionParameters
     * @param array                                                                           $parameters
     *
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @throws \Viserio\Component\Contract\Container\Exception\CyclicDependencyException
     *
     * @return object
     */
    protected function resolveReflectionClass(
        ReflectionClass $reflectionClass,
        array $reflectionParameters,
        array $parameters = []
    ): object {
        $className = $reflectionClass->getName();

        if (\in_array($className, $this->buildStack, true)) {
            $this->buildStack[] = $className;

            throw new CyclicDependencyException($className, $this->buildStack);
        }

        $this->buildStack[] = $className;
        $resolvedParameters = $this->resolveParameters($reflectionParameters, $parameters);

        try {
            if (\count($resolvedParameters) === 0) {
                return new $className();
            }

            return new $className(...$resolvedParameters);
        } finally {
            \array_pop($this->buildStack);
        }
    }

    /**
     * Resolve a method.
     *
     * @param \Roave\BetterReflection\Reflection\ReflectionMethod                             $reflectionMethod
     * @param \ReflectionParameter[]|\Roave\BetterReflection\Reflection\ReflectionParameter[] $reflectionParameters
     * @param array                                                                           $parameters
     *
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @throws \Viserio\Component\Contract\Container\Exception\CyclicDependencyException
     *
     * @return mixed
     */
    protected function resolveReflectionMethod(
        ReflectionMethod $reflectionMethod,
        array $reflectionParameters,
        array $parameters = []
    ) {
        $this->buildStack[] = $reflectionMethod->getName();

        $resolvedParameters = $this->resolveParameters($reflectionParameters, $parameters);

        try {
            return $reflectionMethod->invoke(
                $this->resolveReflectionClass(
                    $classReflector = $reflectionMethod->getImplementingClass(),
                    ReflectionFactory::getParameters($classReflector)
                ),
                $resolvedParameters
            );
        } finally {
            \array_pop($this->buildStack);
        }
    }

    /**
     * Resolve a closure / function from ReflectionFunction.
     *
     * @param \ReflectionFunction|\Roave\BetterReflection\Reflection\ReflectionFunction       $reflectionFunction
     * @param \ReflectionParameter[]|\Roave\BetterReflection\Reflection\ReflectionParameter[] $reflectionParameters
     * @param array                                                                           $parameters
     *
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @throws \Viserio\Component\Contract\Container\Exception\CyclicDependencyException
     * @throws \Viserio\Component\Contract\Container\Exception\RuntimeException
     *
     * @return mixed
     */
    protected function resolveReflectionFunction(
        $reflectionFunction,
        array $reflectionParameters,
        array $parameters = []
    ) {
        $this->buildStack[] = $reflectionFunction->getName();

        $parameters = $this->resolveParameters($reflectionParameters, $parameters);

        try {
            return $reflectionFunction->invokeArgs($parameters);
        } finally {
            \array_pop($this->buildStack);
        }
    }

    /**
     * Resolve parameter class.
     *
     * @param string $class
     *
     * @throws CyclicDependencyException
     *
     * @return object
     */
    abstract protected function resolveParameterClass(string $class): object;

    /**
     * Resolve an array of reflection parameter parameters.
     *
     * @param \ReflectionParameter|\Roave\BetterReflection\Reflection\ReflectionParameter[] $reflectionParameters
     * @param array                                                                         $parameters
     *
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @throws \Viserio\Component\Contract\Container\Exception\CyclicDependencyException
     *
     * @return array
     */
    private function resolveParameters(array $reflectionParameters, array $parameters = []): array
    {
        $dependencies = [];

        foreach ($reflectionParameters as $parameter) {
            $dependencies[] = $this->resolveParameter($parameter, $parameters);
        }

        return $this->mergeParameters($dependencies, $parameters);
    }

    /**
     * Resolve a parameter.
     *
     * @param \ReflectionParameter|\Roave\BetterReflection\Reflection\ReflectionParameter $parameter
     * @param array                                                                       $parameters
     *
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @throws \Viserio\Component\Contract\Container\Exception\CyclicDependencyException
     *
     * @return mixed
     */
    private function resolveParameter($parameter, array $parameters = [])
    {
        $name  = $parameter->getName();
        $index = $parameter->getPosition();

        if (isset($parameters[$name]) && ! $parameter->isVariadic()) {
            $value = $parameters[$name];

            unset($parameters[$parameter->getName()], $parameters[$index]);

            return $value;
        }

        if (isset($parameters[$index])) {
            $value = $parameters[$index];

            unset($parameters[$index]);

            return $value;
        }

        if ($parameter->hasType() && ! $parameter->getType()->isBuiltin()) {
            try {
                $class = $parameter->getClass();
            } catch (BaseRuntimeException $exception) {
                $class = null;
            }

            if ($class === null) {
                if ($parameter->allowsNull()) {
                    return null;
                }

                throw new BindingResolutionException(
                    \sprintf(
                        'Class [%s] needed by [%s] not found. Check type hint and \'use\' statements.',
                        (string) $parameter->getType(),
                        $parameter
                    )
                );
            }

            return $this->resolveParameterClass($class->getName());
        }

        // !optional + defaultAvailable = func($a = null, $b) since 5.4.7
        // optional + !defaultAvailable = i.e. Exception::__construct, mysqli::mysqli, ...
        if ($parameter->isOptional() || $parameter->isDefaultValueAvailable() || ($parameter->hasType() && $parameter->allowsNull())) {
            return $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
        }

        throw new BindingResolutionException(\sprintf(
            'Unresolvable parameter resolving [$%s] in [%s] has no value defined or is not guessable.',
            $parameter,
            \end($this->buildStack)
        ));
    }

    /**
     * Merge some dynamically resolved parameters with some others provided parameters by the user.
     *
     * @param array $rootParameters
     * @param array $parameters
     *
     * @return array
     */
    private function mergeParameters(array $rootParameters, array $parameters = []): array
    {
        foreach ($parameters as $key => $value) {
            if (\is_int($key) && ! isset($rootParameters[$key])) {
                $rootParameters[$key] = $value;
            }
        }

        return $rootParameters;
    }
}
