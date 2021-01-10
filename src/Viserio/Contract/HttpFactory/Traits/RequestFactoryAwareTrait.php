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

use Psr\Http\Message\RequestFactoryInterface;

trait RequestFactoryAwareTrait
{
    /**
     * A RequestFactory instance.
     *
     * @var null|\Psr\Http\Message\RequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * Set a RequestFactory instance.
     *
     * @return static
     */
    public function setRequestFactory(RequestFactoryInterface $requestFactory): self
    {
        $this->requestFactory = $requestFactory;

        return $this;
    }
}
