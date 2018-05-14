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

namespace Viserio\Component\Session\Fingerprint;

use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Support\Http\ClientIp;
use Viserio\Contract\Session\Fingerprint as FingerprintContract;

class ClientIpGenerator implements FingerprintContract
{
    /**
     * Client ip + secret key string.
     *
     * @var string
     */
    private $clientIp;

    /**
     * Create a new ClientIpGenerator instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     */
    public function __construct(ServerRequestInterface $serverRequest)
    {
        $this->clientIp = (new ClientIp($serverRequest))->getIpAddress();
    }

    /**
     * {@inheritdoc}.
     */
    public function generate(): string
    {
        return \hash('ripemd160', $this->clientIp ?? '');
    }
}
