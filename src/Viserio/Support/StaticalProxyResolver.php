<?php
namespace Viserio\Support;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0
 */

/**
 * StaticalProxyResolver.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.1
 */
class StaticalProxyResolver
{
    /**
     * Resolve a facade quickly to its root class.
     *
     * @param string $facade
     *
     * @return string
     */
    public function resolve($facade)
    {
        if ($this->isFacade($this->getFacadeNameFromInput($facade))) {
            $rootClass = get_class($facade::getFacadeRoot());

            return sprintf('The registered facade [%s] maps to [%s]', $this->getFacadeNameFromInput($facade), $rootClass);
        }

        return 'Facade not found';
    }

    /**
     * Create a uppercase facade name if is not already.
     *
     * @param string $facadeName
     *
     * @return string
     */
    public function getFacadeNameFromInput($facadeName)
    {
        if ($this->isUppercase($facadeName)) {
            return $facadeName;
        }

        return ucfirst(Str::camelize(strtolower($facadeName)));
    }

    /**
     * Checking if facade is a really facade of StaticalProxyManager.
     *
     * @param string $facade
     *
     * @return bool
     */
    public function isFacade($facade)
    {
        if (class_exists($facade)) {
            return array_key_exists('Viserio\Application\StaticalProxyManager', class_parents($facade));
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
        return (strtoupper($string) === $string);
    }
}
