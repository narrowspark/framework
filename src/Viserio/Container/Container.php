<?php
declare(strict_types=1);
namespace Viserio\Container;

use ArrayAccess;
use Interop\Container\ContainerInterface;
use InvalidArgumentException;
use Invoker\Invoker;
use Invoker\InvokerInterface;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\Container\ParameterNameContainerResolver;
use Invoker\ParameterResolver\Container\TypeHintContainerResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ResolverChain;
use Viserio\Container\Proxy\ProxyFactory;
use Viserio\Contracts\Container\Container as ContainerContract;

class Container implements ArrayAccess, ContainerInterface, ContainerContract, InvokerInterface
{
    /**
     * The container's bindings.
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * Invoker instance.
     *
     * @var \Invoker\InvokerInterface|null
     */
    private $invoker;

    /**
     * Array full of container implementing the ContainerInterface.
     *
     * @var \Interop\Container\ContainerInterface[]
     */
    protected $delegates = [];

    /**
     * Autowiring to guess injections.
     *
     * Enabled by default.
     *
     * @var bool
     */
    private $useAutowiring = true;

    /**
     * ProxyFactory instance.
     *
     * @var \Viserio\Container\Proxy\ProxyFactory
     */
    private $proxyFactory;

    /**
     * Create a new container instance.
     *
     * @param bool        $writeProxiesToFile
     * @param string|null $proxyDirectory
     */
    public function __construct(bool $writeProxiesToFile, string $proxyDirectory = null)
    {
        $this->proxyFactory = new ProxyFactory($writeProxiesToFile, $proxyDirectory);

        // Auto-register the container
        $this->singleton(Container::class, $this);
        $this->singleton(ContainerContract::class, $this);
        $this->singleton(ContainerInteropInterface::class, $this);
    }

    /**
     * {@inheritdoc}
     */
    public function bind($abstract, $concrete = null)
    {
        $abstract = $this->normalize($abstract);
        $concrete = ($concrete) ? $this->normalize($concrete) : $abstract;

        if (is_array($abstract)) {
            $this->bindService(key($abstract), $concrete);
            $this->alias(key($abstract), current($abstract));
        } else {
            $this->bindService($abstract, $concrete);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function bindIf(string $abstract, $concrete = null)
    {
        if (!$this->has($abstract)) {
            $this->bind($abstract, $concrete);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function instance(string $abstract, $instance)
    {
        $abstract = $this->normalize($abstract);

        if (is_array($abstract)) {
            $this->bindPlain(key($abstract), $instance);
            $this->alias(key($abstract), current($abstract));
        } else {
            $this->bindPlain($abstract, $instance);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function singleton($abstract, $concrete = null)
    {
        $abstract = $this->normalize($abstract);
        $concrete = ($concrete) ? $this->normalize($concrete) : $abstract;

        if (is_array($abstract)) {
            $this->bindSingleton(key($abstract), $concrete);
            $this->alias(key($abstract), current($abstract));
        } else {
            $this->bindSingleton($abstract, $concrete);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function alias(string $abstract, string $alias)
    {
        $alias = $this->normalize($alias);
        $abstract = $this->normalize($abstract);

        $this->bindings[$alias] = &$this->bindings[$abstract];
    }

    /**
     * Returns an entry of the container by its name.
     *
     * @param string $name Entry name or a class name.
     *
     * @throws InvalidArgumentException The name parameter must be of type string.
     * @throws DependencyException      Error while resolving the entry.
     * @throws NotFoundException        No entry found for the given name.
     *
     * @return mixed
     */
    public function get($id)
    {
        if (! is_string($id)) {
            throw new InvalidArgumentException(sprintf(
                'The name parameter must be of type string, %s given',
                is_object($id) ? get_class($id) : gettype($id)
            ));
        }

        if ($resolved = $this->getFromDelegate($alias, $args)) {
        }

        throw new NotFoundException(
            sprintf('Alias (%s) is not being managed by the container', $id)
        );
    }

    /**
     * Test if the container can provide something for the given name.
     *
     * @param string $name Entry name or a class name.
     *
     * @throws InvalidArgumentException The name parameter must be of type string.
     *
     * @return bool
     */
    public function has($id)
    {
        if (! is_string($id)) {
            throw new InvalidArgumentException(sprintf(
                'The name parameter must be of type string, %s given',
                is_object($id) ? get_class($id) : gettype($id)
            ));
        }

        $id = $this->normalize($id);

        if (is_string($id) && isset($this->bindings[$id])) {
            return true;
        }

        return $this->hasInDelegate($id);
    }

    /**
     * Enable or disable the use of autowiring to guess injections.
     *
     * @param bool $bool
     *
     * @return \Viserio\Contracts\Container\Container
     */
    public function useAutowiring(bool $bool): ContainerContract
    {
        $this->useAutowiring = $bool;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function call($callable, array $parameters = [])
    {
        return $this->getInvoker()->call($callable, $parameters);
    }

    /**
     * Delegate a backup container to be checked for services if it
     * cannot be resolved via this container.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return $this
     */
    public function delegate(InteropContainerInterface $container): ContainerContract
    {
        $this->delegates[] = $container;

        return $this;
    }

    /**
     * Returns true if service is registered in one of the delegated backup containers.
     *
     * @param string $alias
     *
     * @return bool
     */
    public function hasInDelegate($abstract)
    {
        foreach ($this->delegates as $container) {
            if ($container->has($abstract)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Set the value at a given offset
     *
     * @param string $offset
     * @param mixed  $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_string($value)) {
            $this->bindPlain($offset, $value);
        } else {
            $this->bindService($offset, $value);
        }
    }

    /**
     * Get the value at a given offset
     *
     * @param string $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->resolve($offset);
    }

    /**
     * Unset the value at a given offset
     *
     * @param string $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->bindings[$offset]);
    }

    /**
     * Determine if a given offset exists
     *
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->bindings[$offset]);
    }

    /**
     * Attempt to get a service from the stack of delegated backup containers.
     *
     * @param string $abstract
     * @param array  $args
     *
     * @return mixed
     */
    protected function getFromDelegate(string $abstract, array $args = [])
    {
        foreach ($this->delegates as $container) {
            if ($container->has($abstract)) {
                return $container->get($abstract, $args);
            }

            continue;
        }

        return false;
    }

    /**
     * Bind a plain value.
     *
     * @param string $abstract
     * @param mixed  $concrete
     */
    protected function bindPlain(string $abstract, $concrete)
    {
        $this->bindings[$abstract] = [
            self::VALUE => $concrete,
            self::IS_RESOLVED => false,
            self::BINDING_TYPE => self::TYPE_PLAIN,
        ];
    }

    /**
     * Bind a value which need to be resolved each time.
     *
     * @param string $abstract
     * @param mixed  $concrete
     */
    protected function bindService(string $abstract, $concrete)
    {
        $this->bindings[$abstract] = [
            self::VALUE => $concrete,
            self::IS_RESOLVED => false,
            self::BINDING_TYPE => self::TYPE_SERVICE,
        ];
    }

    /**
     * Bind a value which need to be resolved one time.
     *
     * @param string $abstract
     * @param mixed  $concrete
     */
    protected function bindSingleton(string $abstract, $concrete)
    {
        $this->bindings[$abstract] = [
            self::VALUE => $concrete,
            self::IS_RESOLVED => false,
            self::BINDING_TYPE => self::TYPE_SINGLETON,
        ];
    }

    /**
     * Normalize the given class name by removing leading slashes.
     *
     * @param mixed $service
     *
     * @return mixed
     */
    private function normalize($service)
    {
        return is_string($service) ? ltrim($service, '\\') : $service;
    }

    /**
     * Get a configured instance of invoker.
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
}
