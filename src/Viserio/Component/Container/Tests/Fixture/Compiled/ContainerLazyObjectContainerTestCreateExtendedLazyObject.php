<?php

declare(strict_types=1);

namespace Viserio\Component\Container\Tests\Integration\Compiled;

/**
 * This class has been auto-generated by Viserio Container Component.
 */
final class ContainerLazyObjectContainerTestCreateExtendedLazyObject extends \Viserio\Component\Container\AbstractCompiledContainer
{
    /**
     * Create a new Compiled Container instance.
     */
    public function __construct()
    {
        $this->services = $this->privates = [];
        $this->methodMapping = [
            'foo' => 'get55df4251026261c15e5362b72748729c5413605491a6b31caf07b0571c04af5f',
        ];
    }

    /**
     * Returns the public foo service.
     *
     * @return \Viserio\Component\Container\Tests\Fixture\Proxy\ClassToProxy
     */
    protected function get55df4251026261c15e5362b72748729c5413605491a6b31caf07b0571c04af5f(): \Viserio\Component\Container\Tests\Fixture\Proxy\ClassToProxy
    {
        return $this->createProxy('ClassToProxy_3eb577cd47e8246b251d424ff6c86b4d79c5eafc5ec07c99c11e3b439e9504ec', static function () {
            return ClassToProxy_3eb577cd47e8246b251d424ff6c86b4d79c5eafc5ec07c99c11e3b439e9504ec::staticProxyConstructor(static function (&$wrappedInstance, \ProxyManager\Proxy\LazyLoadingInterface $proxy) {
                $instance = new \Viserio\Component\Container\Tests\Fixture\Proxy\ClassToProxy();

            $instance->setBar('test');

                $wrappedInstance = $instance;

                $proxy->setProxyInitializer(null);

                return true;
            });
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getRemovedIds(): array
    {
        return [
            \Psr\Container\ContainerInterface::class => true,
            \Viserio\Contract\Container\CompiledContainer::class => true,
            \Viserio\Contract\Container\Factory::class => true,
            \Viserio\Contract\Container\TaggedContainer::class => true,
            'container' => true,
        ];
    }

    /**
     * Invoke a proxy instance.
     *
     * @param string   $class
     * @param \Closure $factory
     *
     * @return object
     */
    protected function createProxy(string $class, \Closure $factory): object
    {
        \class_exists($class, false) || \class_alias("Viserio\\Component\\Container\\Tests\\IntegrationTest\\Compiled\\{$class}", $class, false);

        return $factory();
    }
}

class ClassToProxy_3eb577cd47e8246b251d424ff6c86b4d79c5eafc5ec07c99c11e3b439e9504ec extends \Viserio\Component\Container\Tests\Fixture\Proxy\ClassToProxy implements \ProxyManager\Proxy\VirtualProxyInterface
{
    private $valueHoldercf38d = null;
    private $initializer3844f = null;
    private static $publicProperties7f120 = [
        'foo' => true,
        'moo' => true,
        'bar' => true,
        'initialized' => true,
        'configured' => true,
        'called' => true,
        'arguments' => true,
    ];
    public function __destruct()
    {
        $this->initializer3844f || $this->valueHoldercf38d->__destruct();
    }
    public function setBar($value = null) : void
    {
        $this->initializer3844f && ($this->initializer3844f->__invoke($valueHoldercf38d, $this, 'setBar', array('value' => $value), $this->initializer3844f) || 1) && $this->valueHoldercf38d = $valueHoldercf38d;
        $this->valueHoldercf38d->setBar($value);
return;
    }
    public function initialize() : void
    {
        $this->initializer3844f && ($this->initializer3844f->__invoke($valueHoldercf38d, $this, 'initialize', array(), $this->initializer3844f) || 1) && $this->valueHoldercf38d = $valueHoldercf38d;
        $this->valueHoldercf38d->initialize();
return;
    }
    public function configure() : void
    {
        $this->initializer3844f && ($this->initializer3844f->__invoke($valueHoldercf38d, $this, 'configure', array(), $this->initializer3844f) || 1) && $this->valueHoldercf38d = $valueHoldercf38d;
        $this->valueHoldercf38d->configure();
return;
    }
    public static function staticProxyConstructor($initializer)
    {
        static $reflection;
        $reflection = $reflection ?? new \ReflectionClass(__CLASS__);
        $instance   = $reflection->newInstanceWithoutConstructor();
        unset($instance->foo, $instance->moo, $instance->bar, $instance->initialized, $instance->configured, $instance->called, $instance->arguments);
        $instance->initializer3844f = $initializer;
        return $instance;
    }
    public function __construct(array $arguments = [])
    {
        static $reflection;
        if (! $this->valueHoldercf38d) {
            $reflection = $reflection ?? new \ReflectionClass('Viserio\\Component\\Container\\Tests\\Fixture\\Proxy\\ClassToProxy');
            $this->valueHoldercf38d = $reflection->newInstanceWithoutConstructor();
        unset($this->foo, $this->moo, $this->bar, $this->initialized, $this->configured, $this->called, $this->arguments);
        }
        $this->valueHoldercf38d->__construct($arguments);
    }
    public function & __get($name)
    {
        $this->initializer3844f && ($this->initializer3844f->__invoke($valueHoldercf38d, $this, '__get', ['name' => $name], $this->initializer3844f) || 1) && $this->valueHoldercf38d = $valueHoldercf38d;
        if (isset(self::$publicProperties7f120[$name])) {
            return $this->valueHoldercf38d->$name;
        }
        $realInstanceReflection = new \ReflectionClass(get_parent_class($this));
        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHoldercf38d;
            $backtrace = debug_backtrace(false);
            trigger_error(
                sprintf(
                    'Undefined property: %s::$%s in %s on line %s',
                    get_parent_class($this),
                    $name,
                    $backtrace[0]['file'],
                    $backtrace[0]['line']
                ),
                \E_USER_NOTICE
            );
            return $targetObject->$name;
            return;
        }
        $targetObject = $this->valueHoldercf38d;
        $accessor = function & () use ($targetObject, $name) {
            return $targetObject->$name;
        };
        $backtrace = debug_backtrace(true);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $returnValue = & $accessor();
        return $returnValue;
    }
    public function __set($name, $value)
    {
        $this->initializer3844f && ($this->initializer3844f->__invoke($valueHoldercf38d, $this, '__set', array('name' => $name, 'value' => $value), $this->initializer3844f) || 1) && $this->valueHoldercf38d = $valueHoldercf38d;
        if (isset(self::$publicProperties7f120[$name])) {
            return ($this->valueHoldercf38d->$name = $value);
        }
        $realInstanceReflection = new \ReflectionClass(get_parent_class($this));
        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHoldercf38d;
            return $targetObject->$name = $value;
            return;
        }
        $targetObject = $this->valueHoldercf38d;
        $accessor = function & () use ($targetObject, $name, $value) {
            return $targetObject->$name = $value;
        };
        $backtrace = debug_backtrace(true);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $returnValue = & $accessor();
        return $returnValue;
    }
    public function __isset($name)
    {
        $this->initializer3844f && ($this->initializer3844f->__invoke($valueHoldercf38d, $this, '__isset', array('name' => $name), $this->initializer3844f) || 1) && $this->valueHoldercf38d = $valueHoldercf38d;
        if (isset(self::$publicProperties7f120[$name])) {
            return isset($this->valueHoldercf38d->$name);
        }
        $realInstanceReflection = new \ReflectionClass(get_parent_class($this));
        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHoldercf38d;
            return isset($targetObject->$name);
            return;
        }
        $targetObject = $this->valueHoldercf38d;
        $accessor = function () use ($targetObject, $name) {
            return isset($targetObject->$name);
        };
        $backtrace = debug_backtrace(true);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $returnValue = $accessor();
        return $returnValue;
    }
    public function __unset($name)
    {
        $this->initializer3844f && ($this->initializer3844f->__invoke($valueHoldercf38d, $this, '__unset', array('name' => $name), $this->initializer3844f) || 1) && $this->valueHoldercf38d = $valueHoldercf38d;
        if (isset(self::$publicProperties7f120[$name])) {
            unset($this->valueHoldercf38d->$name);
            return;
        }
        $realInstanceReflection = new \ReflectionClass(get_parent_class($this));
        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHoldercf38d;
            unset($targetObject->$name);
            return;
        }
        $targetObject = $this->valueHoldercf38d;
        $accessor = function () use ($targetObject, $name) {
            unset($targetObject->$name);
        };
        $backtrace = debug_backtrace(true);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $returnValue = $accessor();
        return $returnValue;
    }
    public function __clone()
    {
        $this->initializer3844f && ($this->initializer3844f->__invoke($valueHoldercf38d, $this, '__clone', array(), $this->initializer3844f) || 1) && $this->valueHoldercf38d = $valueHoldercf38d;
        $this->valueHoldercf38d = clone $this->valueHoldercf38d;
    }
    public function __sleep()
    {
        $this->initializer3844f && ($this->initializer3844f->__invoke($valueHoldercf38d, $this, '__sleep', array(), $this->initializer3844f) || 1) && $this->valueHoldercf38d = $valueHoldercf38d;
        return array('valueHoldercf38d');
    }
    public function __wakeup()
    {
        unset($this->foo, $this->moo, $this->bar, $this->initialized, $this->configured, $this->called, $this->arguments);
    }
    public function setProxyInitializer(\Closure $initializer = null) : void
    {
        $this->initializer3844f = $initializer;
    }
    public function getProxyInitializer() : ?\Closure
    {
        return $this->initializer3844f;
    }
    public function initializeProxy() : bool
    {
        return $this->initializer3844f && ($this->initializer3844f->__invoke($valueHoldercf38d, $this, 'initializeProxy', array(), $this->initializer3844f) || 1) && $this->valueHoldercf38d = $valueHoldercf38d;
    }
    public function isProxyInitialized() : bool
    {
        return null !== $this->valueHoldercf38d;
    }
    public function getWrappedValueHolderValue() : ?object
    {
        return $this->valueHoldercf38d;
    }
}
