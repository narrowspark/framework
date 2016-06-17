<?php
namespace Viserio\Session\Fingerprint;

use Viserio\Contracts\Session\Fingerprint as FingerprintContract;

class UserAgentGenerator implements FingerprintContract
{
    /**
     * User agent string.
     *
     * @var string
     */
    private $userAgent;

    /**
     * @param string $secretKey
     * @param string $userAgent
     */
    public function __construct(string $secretKey, string $userAgent = null)
    {
        if ($userAgent !== null) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        }

        $this->userAgent = $secretKey . $userAgent;
    }

    /**
    * {@inhertiddoc}
     */
    public function generate(): string
    {
        return hash('ripemd160', $this->userAgent);
    }
}
