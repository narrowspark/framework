<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Fingerprint;

use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Session\Fingerprint as FingerprintContract;

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
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     */
    public function __construct(ServerRequestInterface $serverRequest)
    {
        $userAgent    = '';
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
