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
     * @param string $userAgent
     */
    public function __construct(string $userAgent = null)
    {
        if ($userAgent !== null) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        }

        $this->userAgent = $userAgent;
    }

    /**
    * {@inhertiddoc}
     */
    public function generate(): string
    {
        return hash('sha1', $this->userAgent);
    }
}
