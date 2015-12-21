<?php
namespace Viserio\Container;

use Viserio\Support\Traits\ContainerAwareTrait;

class Inflector
{
    use ContainerAwareTrait;

    /**
     * @var array
     */
    protected $methods = [];

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * The concrete instance.
     *
     * @var string
     */
    protected $concrete;

    /**
     * Defines a method to be invoked on the subject object.
     *
     * @param string $name
     * @param array  $args
     *
     * @return \Viserio\Container\Inflector
     */
    public function invokeMethod($name, array $args)
    {
        $this->methods[$name] = $args;

        return $this;
    }

    /**
     * Defines multiple methods to be invoked on the subject object.
     *
     * @param array $methods
     *
     * @return \Viserio\Container\Inflector
     */
    public function invokeMethods(array $methods)
    {
        foreach ($methods as $name => $args) {
            $this->invokeMethod($name, $args);
        }

        return $this;
    }

    /**
     * Defines a property to be set on the subject object.
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return \Viserio\Container\Inflector
     */
    public function setProperty($property, $value)
    {
        $this->properties[$property] = $value;

        return $this;
    }

    /**
     * Defines multiple properties to be set on the subject object.
     *
     * @param array $properties
     *
     * @return \Viserio\Container\Inflector
     */
    public function setProperties(array $properties)
    {
        foreach ($properties as $property => $value) {
            $this->setProperty($property, $value);
        }

        return $this;
    }

    /**
     * Apply inflections to an object.
     *
     * @param object $object
     */
    public function inflect($object)
    {
        $properties = $this->resolveArguments(array_values($this->properties));
        $properties = array_combine(array_keys($this->properties), $properties);

        foreach ($properties as $property => $value) {
            $object->{$property} = $value;
        }

        foreach ($this->methods as $name => $args) {
            $args = $this->resolveArguments($args);
            call_user_func_array([$object, $name], $args);
        }
    }

    /**
     * Uses the container to resolve arguments.
     *
     * @param array $args
     *
     * @return array
     */
    public function resolveArguments(array $args)
    {
        $resolved = [];

        foreach ($args as $arg) {
            $resolved[] = (is_string($arg) && $this->bound($arg)) ? $this->getContainer()->get($arg) : $arg;
        }

        return $resolved;
    }

    /**
     * Check if arg is bound.
     *
     * @param string $arg
     *
     * @return bool
     */
    protected function bound($arg)
    {
        return
            $this->getContainer()->isRegistered($arg) ||
            $this->getContainer()->isSingleton($arg) ||
            $this->getContainer()->resolveClassName($arg)
        ;
    }
}
