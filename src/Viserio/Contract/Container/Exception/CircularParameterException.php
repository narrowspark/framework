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

class CircularParameterException extends Exception implements ContainerExceptionInterface
{
    /**
     * All parameters.
     *
     * @var array
     */
    protected $parameters;

    /**
     * Create a new circular parameter exception instance.
     *
     * @param string         $name
     * @param array          $parameters
     * @param null|Exception $previous
     * @param string         $message
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
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
