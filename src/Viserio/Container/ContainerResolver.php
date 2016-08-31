<?php
declare(strict_types=1);
namespace Viserio\Container;

use Closure;
use Reflector;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionParameter;
use Viserio\Contracts\Container\Exceptions\UnresolvableDependencyException;

class ContainerResolver
{
    /**
     * The stack of concretions currently being built.
     *
     * @var array
     */
    protected $buildStack = [];

    /**
     * Resolve a closure, function, method or a class
     *
     * @param  string|array  $subject
     * @param  array         $parameters
     * @return mixed
     */
    public function resolve($subject, array $parameters = [])
    {
        if ($this->isClass($subject)) {
            return $this->resolveClass($subject, $parameters);
        } else if ($this->isMethod($subject)) {
            return $this->resolveMethod($subject, $parameters);
        } else if ($this->isFunction($subject)) {
            return $this->resolveFunction($subject, $parameters);
        }

        $subject = is_object($subject) ? get_class($subject) : gettype($subject);

        throw new UnresolvableDependencyException("[$subject] is not resolvable. Build stack : [".implode(', ', $this->buildStack)."]");
    }

    /**
     * Resolve a class.
     *
     * @param string $subject
     * @param array  $parameters
     *
     * @return mixed
     */
    public function resolveClass(string $class, array $parameters = [])
    {
        $reflectionClass = new ReflectionClass($class);
        $reflectionMethod = $reflectionClass->getConstructor();

        array_push($this->buildStack, $reflectionClass->name);

        if ($reflectionMethod) {
            $reflectionParameters = $reflectionMethod->getParameters();
            $parameters = $this->resolveParameters($reflectionParameters, $parameters);
        }

        array_pop($this->buildStack);

        return $reflectionClass->newInstanceArgs($parameters);
    }

    /**
     * Resolve a method.
     *
     * @param string|array $subject
     * @param array        $parameters
     *
     * @return mixed
     */
    public function resolveMethod($method, array $parameters = [])
    {
        $reflectionMethod = $this->getMethodReflector($method);
        $reflectionParameters = $reflectionMethod->getParameters();

        array_push($this->buildStack, $reflectionMethod->name);

        $resolvedParameters = $this->resolveParameters($reflectionParameters, $parameters);

        array_pop($this->buildStack);

        return call_user_func_array($method, $resolvedParameters);
    }

    /**
     * Resolve a closure / function
     *
     * @param string|\Closure $subject
     * @param array           $parameters
     *
     * @return mixed
     */
    public function resolveFunction($function, array $parameters = [])
    {
        $reflectionFunction = new ReflectionFunction($function);
        $reflectionParameters = $reflectionFunction->getParameters();

        array_push($this->buildStack, $reflectionFunction->name);

        $resolvedParameters = $this->resolveParameters($reflectionParameters, $parameters);

        array_pop($this->buildStack);

        return $reflectionFunction->invokeArgs($resolvedParameters);
    }

    /**
     * Get the reflection object for something.
     *
     * @param mixed $subject
     *
     * @return mixed
     */
    public function getReflector($subject)
    {
        if ($this->isClass($subject)) {
            return new ReflectionClass($subject);
        } else if ($this->isMethod($subject)) {
            return $this->getMethodReflector($subject);
        } else if ($this->isFunction($subject)) {
            return new ReflectionFunction($subject);
        }

        return;
    }

    /**
     * Resolve a parameter
     *
     * @param \ReflectionParameter $parameter
     * @param array                $parameters
     *
     * @return mixed
     */
    protected function resolveParameter(ReflectionParameter $parameter, array $parameters = [])
    {
        $name = $parameter->name;
        $index = $parameter->getPosition();

        if (isset($parameters[$name])) {
            return $parameters[$name];
        }

        if (isset($parameters[$index])) {
            return $parameters[$index];
        }

        if (($class = $parameter->getClass())) {
            return $this->resolve($class->name);
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new UnresolvableDependencyException("Unresolvable dependency resolving [$parameter] in [".end($this->buildStack)."]");
    }

    /**
     * Resolve an array of \ReflectionParameter parameters.
     *
     * @param  array $reflectionParameters
     * @param  array $parameters
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
     * Merge some dynamicly resolved parameters whith some others provided parameters by the user.
     *
     * @param array $rootParameters
     * @param array $parameters
     *
     * @return array
     */
    private function mergeParameters(array $rootParameters, array $parameters = []): array
    {
        foreach ($parameters as $key => $value) {
            if (!isset($rootParameters[$key]) && is_int($key)) {
                $rootParameters[$key] = $value;
            }
        }

        return $rootParameters;
    }

    /**
     * Get the reflection object for a method.
     *
     * @param string|array $method
     *
     * @return \ReflectionMethod
     */
    protected function getMethodReflector($method): ReflectionMethod
    {
        if (is_string($method)) {
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
        return is_string($value) && class_exists($value);
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
        return is_callable($value) && !$this->isFunction($value);
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
        return is_callable($value) && ($value instanceof Closure || is_string($value) && function_exists($value));
    }

    /**
     * Check if something is resolvable.
     *
     * @param mixed $value
     *
     * @return bool
     */
    protected function isResolvable($value): bool
    {
        return $this->isClass($value) || $this->isMethod($value) || $this->isFunction($value);
    }
}
