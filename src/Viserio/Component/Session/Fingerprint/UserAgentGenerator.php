<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Fingerprint;

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
     * @param string|null $userAgent
     */
    public function __construct(string $userAgent = null)
    {
        if ($userAgent !== null) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        }

        $this->userAgent = random_bytes(32) . $userAgent;
    }

    /**
     * {@inhertiddoc}.
     */
    public function generate(): string
    {
        return hash('ripemd160', $this->userAgent);
    }
}
