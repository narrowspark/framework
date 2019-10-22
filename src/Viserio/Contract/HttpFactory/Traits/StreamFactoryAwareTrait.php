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

use Psr\Http\Message\StreamFactoryInterface;

trait StreamFactoryAwareTrait
{
    /**
     * A StreamFactory instance.
     *
     * @var null|\Psr\Http\Message\StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * Set a StreamFactory instance.
     *
     * @param \Psr\Http\Message\StreamFactoryInterface $streamFactory
     *
     * @return static
     */
    public function setStreamFactory(StreamFactoryInterface $streamFactory): self
    {
        $this->streamFactory = $streamFactory;

        return $this;
    }
}
