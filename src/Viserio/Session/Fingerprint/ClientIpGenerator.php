<?php
declare(strict_types=1);
namespace Viserio\Session\Fingerprint;

use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Session\Fingerprint as FingerprintContract;
use Viserio\Support\Http\ClientIp;

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
        $ip = (new ClientIp($serverRequest))->getIpAddress();

        $this->clientIp = random_bytes(32) . $ip;
    }

    /**
     * {@inhertiddoc}.
     */
    public function generate(): string
    {
        return hash('ripemd160', $this->clientIp);
    }
}
