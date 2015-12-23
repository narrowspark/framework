<?php
namespace Viserio\StaticalProxy;

class StaticalProxyResolver
{
    /**
     * Resolve a static proxy quickly to its root class.
     *
     * @param string $static
     *
     * @return string
     */
    public function resolve($static)
    {
        if ($this->isStaticProxy($this->getStaticProxyNameFromInput($static))) {
            $rootClass = get_class($static::getStaticProxyRoot());

            return sprintf(
                'The registered facade [%s] maps to [%s]',
                $this->getStaticProxyNameFromInput($static),
                $rootClass
            );
        }

        return 'Facade not found';
    }

    /**
     * Create a uppercase facade name if is not already.
     *
     * @param string $staticName
     *
     * @return string
     */
    public function getStaticProxyNameFromInput($staticName)
    {
        if ($this->isUppercase($staticName)) {
            return $staticName;
        }

        return ucfirst(Str::camelize(strtolower($staticName)));
    }

    /**
     * Checking if static proxy is a really static proxy of StaticProxy.
     *
     * @param string $static
     *
     * @return bool
     */
    public function isStaticProxy($static)
    {
        if (class_exists($static)) {
            return array_key_exists(StaticProxy::class, class_parents($static));
        }

        return false;
    }

    /**
     * Checking if facade name is in uppercase.
     *
     * @param string $string
     *
     * @return bool
     */
    private function isUppercase($string)
    {
        return strtoupper($string) === $string;
    }
}
