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
use Viserio\Contract\Session\Fingerprint as FingerprintContract;

class UserAgentGenerator implements FingerprintContract
{
    /**
     * User agent string.
     *
     * @var string
     */
    private $userAgent;

    /**
     * Create a new UserAgentGenerator instance.
     */
    public function __construct(ServerRequestInterface $serverRequest)
    {
        $userAgent = '';
        $serverParams = $serverRequest->getServerParams();

        if (isset($serverParams['REMOTE_ADDR'])) {
            $userAgent = $serverParams['REMOTE_ADDR'];
        }

        $this->userAgent = $userAgent;
    }

    /**
     * {@inheritdoc}.
     */
    public function generate(): string
    {
        return \hash('ripemd160', $this->userAgent);
    }
}
