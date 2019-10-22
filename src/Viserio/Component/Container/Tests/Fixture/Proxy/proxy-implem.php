<?php

class SunnyInterface_f0a1354e858d9757119e7f06da91e14735a7cfa4cf1079db2a846fa9d9474b19 implements \ProxyManager\Proxy\VirtualProxyInterface, \Viserio\Component\Container\Tests\Fixture\Proxy\DummyInterface, \Viserio\Component\Container\Tests\Fixture\Proxy\SunnyInterface
{

    private $valueHolder9267c = null;

    private $initializer60e4d = null;

    private static $publicProperties7bdd6 = [
        
    ];

    public function dummy()
    {
        $this->initializer60e4d && ($this->initializer60e4d->__invoke($valueHolder9267c, $this, 'dummy', array(), $this->initializer60e4d) || 1) && $this->valueHolder9267c = $valueHolder9267c;

        if ($this->valueHolder9267c === $returnValue = $this->valueHolder9267c->dummy()) {
            $returnValue = $this;
        }

        return $returnValue;
    }

    public function & dummyRef()
    {
        $this->initializer60e4d && ($this->initializer60e4d->__invoke($valueHolder9267c, $this, 'dummyRef', array(), $this->initializer60e4d) || 1) && $this->valueHolder9267c = $valueHolder9267c;

        if ($this->valueHolder9267c === $returnValue = &$this->valueHolder9267c->dummyRef()) {
            $returnValue = $this;
        }

        return $returnValue;
    }

    public function sunny()
    {
        $this->initializer60e4d && ($this->initializer60e4d->__invoke($valueHolder9267c, $this, 'sunny', array(), $this->initializer60e4d) || 1) && $this->valueHolder9267c = $valueHolder9267c;

        if ($this->valueHolder9267c === $returnValue = $this->valueHolder9267c->sunny()) {
            $returnValue = $this;
        }

        return $returnValue;
    }

    public static function staticProxyConstructor($initializer)
    {
        static $reflection;

        $reflection = $reflection ?? new \ReflectionClass(__CLASS__);
        $instance = $reflection->newInstanceWithoutConstructor();

        $instance->initializer60e4d = $initializer;

        return $instance;
    }

    public function __construct()
    {
        static $reflection;

        if (! $this->valueHolder9267c) {
            $reflection = $reflection ?? new \ReflectionClass(__CLASS__);
            $this->valueHolder9267c = $reflection->newInstanceWithoutConstructor();
        }
    }

    public function & __get($name)
    {
        $this->initializer60e4d && ($this->initializer60e4d->__invoke($valueHolder9267c, $this, '__get', ['name' => $name], $this->initializer60e4d) || 1) && $this->valueHolder9267c = $valueHolder9267c;

        if (isset(self::$publicProperties7bdd6[$name])) {
            return $this->valueHolder9267c->$name;
        }

        $targetObject = $this->valueHolder9267c;

        $backtrace = debug_backtrace(false);
        trigger_error(
            sprintf(
                'Undefined property: %s::$%s in %s on line %s',
                __CLASS__,
                $name,
                $backtrace[0]['file'],
                $backtrace[0]['line']
            ),
            \E_USER_NOTICE
        );
        return $targetObject->$name;
    }

    public function __set($name, $value)
    {
        $this->initializer60e4d && ($this->initializer60e4d->__invoke($valueHolder9267c, $this, '__set', array('name' => $name, 'value' => $value), $this->initializer60e4d) || 1) && $this->valueHolder9267c = $valueHolder9267c;

        $targetObject = $this->valueHolder9267c;

        return $targetObject->$name = $value;
    }

    public function __isset($name)
    {
        $this->initializer60e4d && ($this->initializer60e4d->__invoke($valueHolder9267c, $this, '__isset', array('name' => $name), $this->initializer60e4d) || 1) && $this->valueHolder9267c = $valueHolder9267c;

        $targetObject = $this->valueHolder9267c;

        return isset($targetObject->$name);
    }

    public function __unset($name)
    {
        $this->initializer60e4d && ($this->initializer60e4d->__invoke($valueHolder9267c, $this, '__unset', array('name' => $name), $this->initializer60e4d) || 1) && $this->valueHolder9267c = $valueHolder9267c;

        $targetObject = $this->valueHolder9267c;

        unset($targetObject->$name);
return;
    }

    public function __clone()
    {
        $this->initializer60e4d && ($this->initializer60e4d->__invoke($valueHolder9267c, $this, '__clone', array(), $this->initializer60e4d) || 1) && $this->valueHolder9267c = $valueHolder9267c;

        $this->valueHolder9267c = clone $this->valueHolder9267c;
    }

    public function __sleep()
    {
        $this->initializer60e4d && ($this->initializer60e4d->__invoke($valueHolder9267c, $this, '__sleep', array(), $this->initializer60e4d) || 1) && $this->valueHolder9267c = $valueHolder9267c;

        return array('valueHolder9267c');
    }

    public function __wakeup()
    {
    }

    public function setProxyInitializer(\Closure $initializer = null)
    {
        $this->initializer60e4d = $initializer;
    }

    public function getProxyInitializer()
    {
        return $this->initializer60e4d;
    }

    public function initializeProxy() : bool
    {
        return $this->initializer60e4d && ($this->initializer60e4d->__invoke($valueHolder9267c, $this, 'initializeProxy', array(), $this->initializer60e4d) || 1) && $this->valueHolder9267c = $valueHolder9267c;
    }

    public function isProxyInitialized() : bool
    {
        return null !== $this->valueHolder9267c;
    }

    public function getWrappedValueHolderValue()
    {
        return $this->valueHolder9267c;
    }


}
