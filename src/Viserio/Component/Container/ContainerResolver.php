<?php
declare(strict_types=1);
namespace Viserio\Component\Container;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use Viserio\Component\Container\Util\Reflection;
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
     * Resolves an entry by its name. If given a class name, it will return a new instance of that class.
     *
     * @param mixed $subject
     * @param array $parameters
     *
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @throws \Viserio\Component\Contract\Container\Exception\CyclicDependencyException
     * @throws \ReflectionException
     *
     * @return mixed
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
            '[%s] is not resolvable. Build stack : [%s].',
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
     * @throws \ReflectionException
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
            $parameters = $this->resolveParameters($reflectionMethod->getParameters(), $parameters);
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
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @throws \Viserio\Component\Contract\Container\Exception\CyclicDependencyException
     * @throws \ReflectionException
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
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @throws \Viserio\Component\Contract\Container\Exception\CyclicDependencyException
     * @throws \ReflectionException
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
     * @throws \ReflectionException
     *
     * @return null|\ReflectionClass|\ReflectionFunction|\ReflectionMethod
     */
    public function getReflector($subject)
    {
        if ($this->isClass($subject)) {
            return new ReflectionClass($subject);
        }

        if ($this->isMethod($subject)) {
            return $this->getMethodReflector($subject);
        }

        if ($this->isFunction($subject)) {
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
     * @throws \Viserio\Component\Contract\Container\Exception\CyclicDependencyException
     * @throws \ReflectionException
     *
     * @return mixed
     */
    protected function resolveParameter(ReflectionParameter $parameter, array $parameters = [])
    {
        $name  = $parameter->getName();
        $index = $parameter->getPosition();

        if (! $parameter->isVariadic() &&isset($parameters[$name])) {
            $value = $parameters[$name];

            unset($parameters[$parameter->getName()], $parameters[$index]);

            return $value;
        }

        if (isset($parameters[$index])) {
            $value = $parameters[$index];

            unset($parameters[$index]);

            return $value;
        }

        $type = Reflection::getParameterType($parameter);

        if ($type !== null && ! Reflection::isBuiltinType($type)) {
            try {
                $class = $parameter->getClass();
            } catch (ReflectionException $exception) {
                $class = null;
            }

            if ($class === null) {
                if ($parameter->allowsNull()) {
                    return null;
                }

                throw new BindingResolutionException(sprintf('Class [%s] needed by [%s] not found. Check type hint and \'use\' statements.', $type, $parameter));
            }

            return $this->resolve($class->name);
        }

        // !optional + defaultAvailable = func($a = null, $b) since 5.4.7
        // optional + !defaultAvailable = i.e. Exception::__construct, mysqli::mysqli, ...
        if (($type !== null && $parameter->allowsNull()) || $parameter->isOptional() || $parameter->isDefaultValueAvailable()) {
            return $parameter->isDefaultValueAvailable() ? Reflection::getParameterDefaultValue($parameter) : null;
        }

        throw new BindingResolutionException(\sprintf(
            'Unresolvable dependency resolving [%s] in [%s].',
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
     * @throws \Viserio\Component\Contract\Container\Exception\BindingResolutionException
     * @throws \Viserio\Component\Contract\Container\Exception\CyclicDependencyException
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
     * @throws \ReflectionException
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
        return \is_callable($value) && ($value instanceof Closure || (\is_string($value) && \function_exists($value)));
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
