<?php

namespace Brainwave\Container\Exception;

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
 * @version     0.9.8-dev
 */

class CircularReferenceException extends \Exception
{
    /**
     * The build stack that caused the exception.
     *
     * @var array
     */
    protected $buildStack;

    /**
     * Create a new circular reference exception instance.
     *
     * @param string $class
     * @param array  $buildStack
     */
    public function __construct($class, array $buildStack)
    {
        $this->message = sprintf('Circular reference found while resolving [%s].', $class);
        $this->buildStack = $buildStack;
    }

    /**
     * Get the build stack that caused the exception.
     *
     * @return array
     */
    public function getBuildStack()
    {
        return $this->buildStack;
    }
}
