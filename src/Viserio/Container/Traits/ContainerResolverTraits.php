<?php
namespace Viserio\Container\Traits;

use Viserio\Container\Exception\BindingResolutionException;
use Viserio\Container\Exception\CircularReferenceException;
use Viserio\Container\Exception\UnresolvableDependencyException;

/**
 * ContainerResolverTraut.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
trait ContainerResolverTraits
{
    /**
     * Resolve the given binding.
     *
     * @param string $binding The binding to resolve.
     * @param bool   $alias   Should we resolve aliases?
     *
     * @return mixed The results of invoking the binding callback.
     */
    public function resolve($binding, $alias = true)
    {
        $rawObject = $this->getRaw($binding);

        // If the abstract is not registered, do it now for easy resolution.
        if (null === $rawObject) {
            // Pass $binding to both so it doesn't need to check if null again.
            $this->bind($binding, $binding);
            $rawObject = $this->getRaw($binding);
        }

        if ($alias && isset($this->aliases[$binding])) {
            return $this->resolve($this->aliases[$binding], false);
        }

        return $rawObject($this);
    }

    /**
     * Get the raw object prior to resolution.
     *
     * @param string $binding The $binding key to get the raw value from.
     *
     * @return Definition|\Closure|null Value of the $binding.
     */
    abstract public function getRaw($binding);

    /**
     * Register a binding with the container.
     *
     * @param string      $alias
     * @param string|null $concrete
     * @param bool        $singleton
     */
    abstract public function bind(string $alias, $concrete = null, $singleton = false);

    /**
     * Returns absolute class name - always with leading backslash.
     *
     * @param string $className
     *
     * @return string
     */
    abstract protected function absoluteClassName($className);

    /**
     * Checks if class exists.
     *
     * @param string $className
     *
     * @return string|null
     */
    protected function resolveClassName($className)
    {
        if (class_exists($className)) {
            return $this->absoluteClassName($className);
        }
    }

    /**
     * Reflect on a class, establish it's dependencies.
     *
     * @param string $concrete
     * @param array  $parameters
     *
     * @throws BindingResolutionException
     * @throws CircularReferenceException
     * @throws UnresolvableDependencyException
     *
     * @return \ReflectionClass
     */
    protected function reflect($concrete, array $parameters = [])
    {
        // try to reflect on the class so we can build a definition
        $reflector = new \ReflectionClass($concrete);

        if (! $reflector->isInstantiable()) {
            throw new BindingResolutionException(
                sprintf(
                    'Unable to reflect on the class [%s], does the class exist and is it properly autoloaded?',
                    $concrete
                )
            );
        }

        if (in_array($concrete, $this->buildStack, true)) {
            $this->buildStack[] = $concrete;
            throw new CircularReferenceException($concrete, $this->buildStack);
        }

        $this->buildStack[] = $concrete;
        $constructor = $reflector->getConstructor();

        // If there are no constructors, that means there are no dependencies then
        // we can just resolve the instances of the objects right away, without
        // resolving any other types or dependencies out of these containers.
        if (null === $constructor) {
            array_pop($this->buildStack);

            return new $concrete();
        }

        // Once we have all the constructor's parameters we can create each of the
        // dependency instances and then use the reflection instances to make a
        // new instance of this class, injecting the created dependencies in.
        $parameters = $this->keyParametersByArgument(
            $constructor->getParameters(),
            $parameters
        );

        $dependencies = $this->getDependencies(
            $constructor,
            $parameters
        );

        array_pop($this->buildStack);

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Recursively build the dependency list for the provided method.
     *
     * @param \ReflectionMethod $method     The method for which to obtain dependencies.
     * @param array             $primitives
     *
     * @throws \Viserio\Container\Exception\UnresolvableDependencyException
     *
     * @return array An array containing the method dependencies.
     */
    protected function getDependencies(\ReflectionMethod $method, array $primitives = [])
    {
        $dependencies = [];

        foreach ($method->getParameters() as $parameter) {
            $dependency = $parameter->getClass();

            // If the class is null, it means the dependency is a string or some other
            // primitive type which we can not resolve since it is not a class and
            // we will just bomb out with an error since we have no-where to go.
            if (array_key_exists($parameter->name, $primitives)) {
                $dependencies[] = $primitives[$parameter->name];
            } elseif (is_null($dependency)) {
                if ($parameter->isOptional()) {
                    $dependencies[] = $parameter->getDefaultValue();
                    continue;
                }
            } else {
                $dependencies[] = $this->resolve($dependency->name);
                continue;
            }

            throw new UnresolvableDependencyException(
                sprintf(
                    'Unresolvable dependency resolving [%s] in class %s',
                    $parameter,
                    $parameter->getDeclaringClass()->getName()
                )
            );
        }

        return (array) $dependencies;
    }

    /**
     * If extra parameters are passed by numeric ID, rekey them by argument name.
     *
     * @param array $dependencies
     * @param array $parameters
     *
     * @return array
     */
    protected function keyParametersByArgument(array $dependencies, array $parameters)
    {
        foreach ($parameters as $key => $value) {
            if (is_numeric($key)) {
                unset($parameters[$key]);
                $parameters[$dependencies[$key]->name] = $value;
            }
        }

        return $parameters;
    }
}
