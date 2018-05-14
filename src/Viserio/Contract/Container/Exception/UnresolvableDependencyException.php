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
     * @param string          $id
     * @param string          $message
     * @param int             $code
     * @param null|\Exception $previous
     */
    public function __construct(string $id, $message = '', int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->id = $id;
    }

    /**
     * Get binding id.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
