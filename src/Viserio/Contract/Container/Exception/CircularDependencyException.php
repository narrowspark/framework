<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Contract\Container\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;

class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    /**
     * Name of the class.
     *
     * @var array
     */
    protected $class;

    /**
     * The build stack that caused the exception.
     *
     * @var array
     */
    protected $buildStack;

    /**
     * Create a new circular reference exception instance.
     *
     * @param string          $class
     * @param array           $buildStack
     * @param null|\Exception $previous
     * @param string          $message
     */
    public function __construct(string $class, array $buildStack, Exception $previous = null, string $message = null)
    {
        parent::__construct(\sprintf(
            'Circular reference detected for service [%s]; path: [%s].%s',
            $class,
            \implode(' -> ', $buildStack),
            $message !== null ? ' ' . $message : ''
        ), 0, $previous);

        $this->class = $class;
        $this->buildStack = $buildStack;
    }

    /**
     * Returns the name of the class.
     *
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Get the build stack that caused the exception.
     *
     * @return array
     */
    public function getBuildStack(): array
    {
        return $this->buildStack;
    }
}
