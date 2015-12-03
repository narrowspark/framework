<?php
namespace Viserio\Container\Exception;

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
