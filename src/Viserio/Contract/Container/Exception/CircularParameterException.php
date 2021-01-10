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

class CircularParameterException extends Exception implements ContainerExceptionInterface
{
    /**
     * All parameters.
     */
    protected $parameters;

    /**
     * Create a new circular parameter exception instance.
     *
     * @param string $message
     */
    public function __construct(string $name, array $parameters, ?Exception $previous = null, ?string $message = null)
    {
        parent::__construct(\sprintf(
            'Circular reference detected for parameter [%s]; path: [%s -> %s].%s',
            $name,
            $name,
            \implode(' -> ', $parameters),
            $message !== null ? ' ' . $message : ''
        ), 0, $previous);

        $this->parameters = $parameters;
    }

    /**
     * Returns parameters.
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
