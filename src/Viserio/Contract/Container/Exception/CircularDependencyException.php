<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
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
     * @param string $message
     */
    public function __construct(string $class, array $buildStack, ?Exception $previous = null, ?string $message = null)
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
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Get the build stack that caused the exception.
     */
    public function getBuildStack(): array
    {
        return $this->buildStack;
    }
}
