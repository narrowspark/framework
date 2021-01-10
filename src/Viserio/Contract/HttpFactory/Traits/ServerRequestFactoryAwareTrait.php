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
     * @return static
     */
    public function setServerRequestFactory(ServerRequestFactoryInterface $serverRequestFactory): self
    {
        $this->serverRequestFactory = $serverRequestFactory;

        return $this;
    }
}
