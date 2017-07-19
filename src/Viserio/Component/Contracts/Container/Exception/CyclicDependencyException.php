<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Container\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;

class CyclicDependencyException extends Exception implements ContainerExceptionInterface
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
        parent::__construct(\sprintf('Circular reference found while resolving [%s].', $class));

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
