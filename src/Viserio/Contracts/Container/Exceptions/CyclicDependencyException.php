<?php
declare(strict_types=1);
namespace Viserio\Contracts\Container\Exceptions;

use Exception;
use Interop\Container\Exception\ContainerException as InteropContainerException;

class CyclicDependencyException extends Exception implements InteropContainerException
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
    public function __construct(string $class, array $buildStack)
    {
        $this->message = sprintf('Circular reference found while resolving [%s].', $class);
        $this->buildStack = $buildStack;
    }

    /**
     * Get the build stack that caused the exception.
     *
     * @return array
     *
     * @codeCoverageIgnore
     */
    public function getBuildStack()
    {
        return $this->buildStack;
    }
}
