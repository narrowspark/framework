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

class UnresolvableDependencyException extends Exception implements ContainerExceptionInterface
{
    /**
     * The binding id.
     *
     * @var string
     */
    private $id;

    /**
     * Create a new UnresolvableDependencyException instance.
     *
     * @param string $message
     */
    public function __construct(string $id, $message = '', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->id = $id;
    }

    /**
     * Get binding id.
     */
    public function getId(): string
    {
        return $this->id;
    }
}
