<?php
namespace Viserio\Container;

use Viserio\Contracts\Container\ContainerAware as ContainerAwareContract;

/**
 * Definition.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
class Definition
{
    /**
     * Array of arguments to pass to the class constructor.
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The class name for this Definition.
     *
     * @var string
     */
    protected $class;

    /**
     * The holding container.
     *
     * @var Container
     */
    protected $container;

    /**
     * Method to call on the newly created object for injection.
     *
     * @var array
     */
    protected $methods = [];

    /**
     * Constructor.
     *
     * @param Container $container
     * @param string    $class
     */
    public function __construct(Container $container, $class)
    {
        $this->container = $container;
        $this->class = $class;
    }

    /**
     * Magic method. Runs when using this class as a function.
     * Ex: $object = new Definition($container, $class);
     *     $invoked = $object();.
     *
     * @return object The instantiated $class with optional args passed to the constructor and methods called.
     */
    public function __invoke()
    {
        $this->mergeInheritedDependencies();

        $object = $this->container->build($this->class, $this->arguments);

        $this->hasContainerContract($object);

        return $this->callMethods($object);
    }

    /**
     * Get the arguments to be passed to the classes constructor.
     *
     * @return array
     */
    public function getArgument()
    {
        return $this->arguments;
    }

    /**
     * Get the methods to be called after instantiating.
     *
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Add an argument to the class's constructor.
     *
     * @param string $arg The argument to add. Can be a class name.
     *
     * @return Definition
     */
    public function withArgument($arg)
    {
        $this->arguments[] = $arg;

        return $this;
    }

    /**
     * Add multiple arguments to the class's constructor.
     *
     * @param array $args An array of arguments.
     *
     * @return Definition
     */
    public function withArguments(array $args)
    {
        foreach ($args as $arg) {
            $this->arguments[] = $arg;
        }

        return $this;
    }

    /**
     * Remove all available arguments.
     *
     * @return Definition
     */
    public function cleanArgument()
    {
        $this->arguments = [];

        return $this;
    }

    /**
     * Adds a method call to be executed after instantiating.
     *
     * @param string $method The method name to call.
     * @param array  $args   Array of arguments to pass to the call.
     *
     * @return Definition
     */
    public function withMethodCall($method, array $args = [])
    {
        $this->methods[$method] = $args;

        return $this;
    }

    /**
     * Adds multiple method calls to be executed after instantiating.
     *
     * @param array $methods Array of methods to call with args.
     *
     * @return Definition
     */
    public function withMethodCalls(array $methods)
    {
        foreach ($methods as $method => $args) {
            $this->withMethodCall($method, $args);
        }

        return $this;
    }

    /**
     * Check if class has a container contract.
     *
     * @param object $object
     */
    protected function hasContainerContract($object)
    {
        if ($object instanceof ContainerAwareContract) {
            $this->withMethodCall('setContainer', [$this->container]);
        }
    }

    /**
     * Execute the methods added via call().
     *
     * @param object $object The instatiated $class on which to call the methods.
     *
     * @return mixed The created object
     */
    protected function callMethods($object)
    {
        if (!empty($this->methods)) {
            foreach (array_reverse($this->methods) as $method => $args) {
                $reflection = new \ReflectionMethod($object, $method);
                $arguments = [];

                foreach ($args as $arg) {
                    if (is_string($arg) && (class_exists($arg) || $this->container->bound($arg))) {
                        $arguments[] = $this->container->resolve($arg);
                        continue;
                    }

                    $arguments[] = $arg;
                }

                $reflection->invokeArgs($object, $arguments);
            }
        }

        return $object;
    }

    /**
     * Add methods and args from inherited classes/interfaces.
     */
    protected function mergeInheritedDependencies()
    {
        $reflection = new \ReflectionClass($this->class);

        $inheritance = $reflection->getInterfaceNames();
        $class = $reflection;

        while ($parent = $class->getParentClass()) {
            $inheritance[] = $parent->getName();
            $class = $parent;
        }

        foreach ($inheritance as $interface) {
            $interface = $this->container->getRaw($interface);

            if ($interface instanceof static) {
                $this->withArguments($interface->getArgument());
                $this->withMethodCalls($interface->getMethods());
            }
        }
    }

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

        return;
    }

    /**
     * Returns absolute class name - always with leading backslash.
     *
     * @param string $className
     *
     * @return string
     */
    protected function absoluteClassName($className)
    {
        return substr($className, 0, 1) === '\\' ? $className : '\\' . $className;
    }
}
