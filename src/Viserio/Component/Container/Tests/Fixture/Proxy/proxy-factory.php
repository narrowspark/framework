<?php

return new class
{
    public $proxyClass;
    private $privates = [];

    public function get361043af246e7c8166dac3d9521cd38ed69a8d2f9075493f181c581560d7b289()
    {
        return $this->privates['foo'] = $this->createProxy('SunnyInterface_f0a1354e858d9757119e7f06da91e14735a7cfa4cf1079db2a846fa9d9474b19', static function () {
            return SunnyInterface_f0a1354e858d9757119e7f06da91e14735a7cfa4cf1079db2a846fa9d9474b19::staticProxyConstructor(static function (&$wrappedInstance, \ProxyManager\Proxy\LazyLoadingInterface $proxy) {
                $wrappedInstance = new \Viserio\Component\Container\Tests\Fixture\Proxy\FinalDummyClass();

                $proxy->setProxyInitializer(null);

                return true;
            });
        });
    }

    protected function createProxy($class, \Closure $factory)
    {
        $this->proxyClass = $class;

        return $factory();
    }
};
