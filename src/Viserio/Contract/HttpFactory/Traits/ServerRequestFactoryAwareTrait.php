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

use Psr\Http\Message\ServerRequestFactoryInterface;

trait ServerRequestFactoryAwareTrait
{
    /**
     * A ServerRequest instance.
     *
     * @var null|\Psr\Http\Message\ServerRequestFactoryInterface
     */
    protected $serverRequestFactory;

    /**
     * Set a ServerRequest instance.
     *
     * @param \Psr\Http\Message\ServerRequestFactoryInterface $serverRequestFactory
     *
     * @return static
     */
    public function setServerRequestFactory(ServerRequestFactoryInterface $serverRequestFactory): self
    {
        $this->serverRequestFactory = $serverRequestFactory;

        return $this;
    }
}
