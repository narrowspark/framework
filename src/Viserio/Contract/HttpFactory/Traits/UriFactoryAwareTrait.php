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

use Psr\Http\Message\UriFactoryInterface;

trait UriFactoryAwareTrait
{
    /**
     * A UriFactory instance.
     *
     * @var null|\Psr\Http\Message\UriFactoryInterface
     */
    protected $uriFactory;

    /**
     * Set a UriFactory instance.
     *
     * @param \Psr\Http\Message\UriFactoryInterface $uriFactory
     *
     * @return static
     */
    public function setUriFactory(UriFactoryInterface $uriFactory): self
    {
        $this->uriFactory = $uriFactory;

        return $this;
    }
}
