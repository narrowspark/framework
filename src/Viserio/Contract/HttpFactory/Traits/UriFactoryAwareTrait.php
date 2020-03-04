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
     * @return static
     */
    public function setUriFactory(UriFactoryInterface $uriFactory): self
    {
        $this->uriFactory = $uriFactory;

        return $this;
    }
}
