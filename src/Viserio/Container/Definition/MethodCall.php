<?php
namespace Viserio\Container\Definition;

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
 * @version     0.10.0-dev
 */

use Interop\Container\Definition\MethodCallInterface;

/**
 * MethodCall.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0-dev
 */
class MethodCall implements MethodCallInterface
{
    /**
     * @var string
     */
    private $methodName;

    /**
     * @var scalar|ReferenceInterface
     */
    private $arguments = [];

    /**
     * @param string $methodName
     * @param array $arguments Array of scalar or ReferenceInterface
     */
    public function __construct($methodName, array $arguments)
    {
        $this->methodName = $methodName;
        $this->arguments = $arguments;
    }

    public function getMethodName()
    {
        return $this->methodName;
    }

    public function getArguments()
    {
        return $this->arguments;
    }
}
