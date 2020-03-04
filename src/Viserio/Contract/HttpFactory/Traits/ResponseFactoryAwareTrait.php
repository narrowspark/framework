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

namespace Viserio\Contract\HttpFactory\Traits;

use Psr\Http\Message\ResponseFactoryInterface;

trait ResponseFactoryAwareTrait
{
    /**
     * A ResponseFactory instance.
     *
     * @var null|\Psr\Http\Message\ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * Set a ResponseFactory instance.
     *
     * @return static
     */
    public function setResponseFactory(ResponseFactoryInterface $responseFactory): self
    {
        $this->responseFactory = $responseFactory;

        return $this;
    }
}
