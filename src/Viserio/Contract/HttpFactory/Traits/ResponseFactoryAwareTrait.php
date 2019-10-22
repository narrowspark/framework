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
     * @param \Psr\Http\Message\ResponseFactoryInterface $responseFactory
     *
     * @return static
     */
    public function setResponseFactory(ResponseFactoryInterface $responseFactory): self
    {
        $this->responseFactory = $responseFactory;

        return $this;
    }
}
