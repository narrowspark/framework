<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy;

use Viserio\Component\Support\Str;

class StaticalProxyResolver
{
    /**
     * Resolve a static proxy quickly to its root class.
     *
     * @param string $static
     *
     * @return string
     */
    public function resolve(string $static): string
    {
        if ($this->isStaticProxy($this->getStaticProxyNameFromInput($static))) {
            $rootClass = get_class($static::getStaticalProxyRoot());

            return sprintf(
                'The registered static proxy [%s] maps to [%s]',
                $this->getStaticProxyNameFromInput($static),
                $rootClass
            );
        }

        return 'No static proxy found!';
    }

    /**
     * Create a uppercase facade name if is not already.
     *
     * @param string $staticName
     *
     * @return string
     */
    public function getStaticProxyNameFromInput(string $staticName): string
    {
        if ($this->isUppercase($staticName)) {
            return $staticName;
        }

        return ucfirst((string) Str::camelize(mb_strtolower($staticName)));
    }

    /**
     * Checking if static proxy is a really static proxy of StaticalProxy.
     *
     * @param string $static
     *
     * @return bool
     */
    public function isStaticProxy(string $static): bool
    {
        if (class_exists($static)) {
            return array_key_exists(StaticalProxy::class, class_parents($static));
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
        return mb_strtoupper($string) === $string;
    }
}
