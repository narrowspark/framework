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
     * @return static
     */
    public function setStreamFactory(StreamFactoryInterface $streamFactory): self
    {
        $this->streamFactory = $streamFactory;

        return $this;
    }
}
