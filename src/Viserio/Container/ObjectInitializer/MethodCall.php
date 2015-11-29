<?php
namespace Viserio\Container\ObjectInitializer;

use Interop\Container\Definition\ObjectInitializer\MethodCallInterface;
use Interop\Container\Definition\ReferenceInterface;

class MethodCall implements MethodCallInterface
{
    /**
     * @var string
     */
    private $methodName;

    /**
     * @var mixed[]|ReferenceInterface[] Array of scalar or ReferenceInterface
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
