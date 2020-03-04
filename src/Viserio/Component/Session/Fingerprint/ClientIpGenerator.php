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
