<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Fingerprint;

use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Session\Fingerprint as FingerprintContract;
use Viserio\Component\Support\Http\ClientIp;

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
