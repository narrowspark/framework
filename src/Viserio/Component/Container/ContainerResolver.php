<?php
declare(strict_types=1);
namespace Viserio\Component\Container;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use Viserio\Component\Contract\Container\Exception\BindingResolutionException;
use Viserio\Component\Contract\Container\Exception\CyclicDependencyException;

class ContainerResolver
{
    /**
     * The stack of concretions currently being built.
     *
     * @var array
     */
    protected $buildStack = [];

    /**
     * @param mixed $subject
     * @param array $parameters
     *
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @throws \Viserio\Component\Contract\Container\Exception\CyclicDependencyException
     *
     * @return mixed|object
     */
    public function resolve($subject, array $parameters = [])
    {
        if ($this->isClass($subject)) {
            return $this->resolveClass($subject, $parameters);
        }

        if ($this->isMethod($subject)) {
            return $this->resolveMethod($subject, $parameters);
        }

        if ($this->isFunction($subject)) {
            return $this->resolveFunction($subject, $parameters);
        }

        $subject = \is_object($subject) ? \get_class($subject) : $subject;

        throw new BindingResolutionException(\sprintf(
            '[%s] is not resolvable. Build stack : [%s]',
            $subject,
            \implode(', ', $this->buildStack)
        ));
    }

    /**
     * Resolve a class.
     *
     * @param string $class
     * @param array  $parameters
     *
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @throws \Viserio\Component\Contract\Container\Exception\CyclicDependencyException
     *
     * @return object
     */
    public function resolveClass(string $class, array $parameters = []): object
    {
        $reflectionClass = new ReflectionClass($class);

        if (! $reflectionClass->isInstantiable()) {
            throw new BindingResolutionException(
                \sprintf(
                    'Unable to reflect on the class [%s], does the class exist and is it properly autoloaded?',
                    $class
                )
            );
        }

        if (\in_array($class, $this->buildStack, true)) {
            $this->buildStack[] = $class;

            throw new CyclicDependencyException($class, $this->buildStack);
        }

        $reflectionMethod   = $reflectionClass->getConstructor();
        $this->buildStack[] = $reflectionClass->name;

        if ($reflectionMethod) {
            $reflectionParameters = $reflectionMethod->getParameters();
            $parameters           = $this->resolveParameters($reflectionParameters, $parameters);
        }

        \array_pop($this->buildStack);

        return $reflectionClass->newInstanceArgs($parameters);
    }

    /**
     * Resolve a method.
     *
     * @param array|string $method
     * @param array        $parameters
     *
     * @return mixed
     */
    public function resolveMethod($method, array $parameters = [])
    {
        $reflectionMethod     = $this->getMethodReflector($method);
        $reflectionParameters = $reflectionMethod->getParameters();
        $this->buildStack[]   = $reflectionMethod->name;
        $resolvedParameters   = $this->resolveParameters($reflectionParameters, $parameters);

        \array_pop($this->buildStack);

        return $method(...$resolvedParameters);
    }

    /**
     * Resolve a closure / function.
     *
     * @param callable|string $function
     * @param array           $parameters
     *
     * @return mixed
     */
    public function resolveFunction($function, array $parameters = [])
    {
        $reflectionFunction   = new ReflectionFunction($function);
        $reflectionParameters = $reflectionFunction->getParameters();
        $this->buildStack[]   = $reflectionFunction->name;
        $resolvedParameters   = $this->resolveParameters($reflectionParameters, $parameters);

        \array_pop($this->buildStack);

        return $reflectionFunction->invokeArgs($resolvedParameters);
    }

    /**
     * Get the reflection object for something.
     *
     * @param mixed $subject
     *
     * @return null|\ReflectionClass|\ReflectionFunction|\ReflectionMethod
     *
     * @codeCoverageIgnore
     */
    public function getReflector($subject)
    {
        if ($this->isClass($subject)) {
            return new ReflectionClass($subject);
        }

        if ($this->isMethod($subject)) {
            return $this->getMethodReflector($subject);
        } elseif ($this->isFunction($subject)) {
            return new ReflectionFunction($subject);
        }

        return null;
    }

    /**
     * Resolve a parameter.
     *
     * @param \ReflectionParameter $parameter
     * @param array                $parameters
     *
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     *
     * @return mixed
     */
    protected function resolveParameter(ReflectionParameter $parameter, array $parameters = [])
    {
        $name  = $parameter->name;
        $index = $parameter->getPosition();

        if (isset($parameters[$name])) {
            return $parameters[$name];
        }

        if (isset($parameters[$index])) {
            return $parameters[$index];
        }

        if ($class = $parameter->getClass()) {
            return $this->resolve($class->name);
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new BindingResolutionException(\sprintf(
            'Unresolvable dependency resolving [%s] in [%s]',
            $parameter,
            \end($this->buildStack)
        ));
    }

    /**
     * Resolve an array of \ReflectionParameter parameters.
     *
     * @param array $reflectionParameters
     * @param array $parameters
     *
     * @return array
     */
    protected function resolveParameters(array $reflectionParameters, array $parameters = []): array
    {
        $dependencies = [];

        foreach ($reflectionParameters as $key => $parameter) {
            $dependencies[] = $this->resolveParameter($parameter, $parameters);
        }

        return $this->mergeParameters($dependencies, $parameters);
    }

    /**
     * Get the reflection object for a method.
     *
     * @param array|string $method
     *
     * @return \ReflectionMethod
     */
    protected function getMethodReflector($method): ReflectionMethod
    {
        if (\is_string($method)) {
            return new ReflectionMethod($method);
        }

        return new ReflectionMethod($method[0], $method[1]);
    }

    /**
     * Check if something is a class.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function isClass($value): bool
    {
        return \is_string($value) && \class_exists($value);
    }

    /**
     * Check if something is a method.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function isMethod($value): bool
    {
        return \is_callable($value) && ! $this->isFunction($value);
    }

    /**
     * Check if something is a function.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function isFunction($value): bool
    {
        return \is_callable($value) && ($value instanceof Closure || \is_string($value) && \function_exists($value));
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
