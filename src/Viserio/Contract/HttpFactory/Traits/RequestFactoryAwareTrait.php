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
     * @param \Psr\Http\Message\RequestFactoryInterface $requestFactory
     *
     * @return static
     */
    public function setRequestFactory(RequestFactoryInterface $requestFactory): self
    {
        $this->requestFactory = $requestFactory;

        return $this;
    }
}
