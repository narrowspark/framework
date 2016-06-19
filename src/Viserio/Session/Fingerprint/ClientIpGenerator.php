<?php
namespace Viserio\Session\Fingerprint;

use Defuse\Crypto\Key;
use Viserio\Contracts\Session\Fingerprint as FingerprintContract;

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
     * @param Key $secretKey
     */
    public function __construct(Key $secretKey)
    {
        $clientIp = $this->getIpAddress();

        $this->clientIp = $secretKey->saveToAsciiSafeString() . $clientIp;
    }

    /**
    * {@inhertiddoc}
     */
    public function generate(): string
    {
        return hash('ripemd160', $this->clientIp);
    }

     /**
     * Returns client IP address.
     *
     * @return string IP address.
     */
    private function getIpAddress(): string
    {
        if ($ip = $this->getIpAddressFromProxy()) {
            return $ip;
        }

        // direct IP address
        if (isset($_SERVER['REMOTE_ADDR'])) {
            return filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP|FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE);
        }

        return '';
    }

    /**
     * Attempt to get the IP address for a proxied client
     *
     * @see http://tools.ietf.org/html/draft-ietf-appsawg-http-forwarded-10#section-5.2
     *
     * @return false|string
     */
    private function getIpAddressFromProxy()
    {
        $header = 'HTTP_X_FORWARDED_FOR';

        if (!isset($_SERVER[$header]) || empty($_SERVER[$header])) {
            return false;
        }

        // Extract IPs
        $ips = explode(',', $_SERVER[$header]);
        $ips = array_map('trim', $ips);

        if (empty($ips)) {
            return false;
        }

        // Since we've removed any known, trusted proxy servers, the right-most
        // address represents the first IP we do not know about -- i.e., we do
        // not know if it is a proxy server, or a client. As such, we treat it
        // as the originating IP.
        // @see http://en.wikipedia.org/wiki/X-Forwarded-For
        $ip = array_pop($ips);

        return filter_var(end($ip), FILTER_VALIDATE_IP|FILTER_FLAG_NO_PRIV_RANGE|FILTER_FLAG_NO_RES_RANGE);
    }
}
