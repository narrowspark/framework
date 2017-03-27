<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Http;

use Psr\Http\Message\ServerRequestInterface;

class ClientIp
{
    /**
     * A server request instance.
     *
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $serverRequest;

    /**
     * Create ClientIp instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     */
    public function __construct(ServerRequestInterface $serverRequest)
    {
        $this->serverRequest = $serverRequest;
    }

    /**
     * Returns client IP address.
     *
     * @return string
     */
    public function getIpAddress(): string
    {
        if (($ip = $this->getIpAddressFromProxy()) !== null) {
            return $ip;
        }

        // direct IP address
        if ($this->serverRequest->hasHeader('REMOTE_ADDR')) {
            $ip = filter_var(
                $this->serverRequest->getHeader('REMOTE_ADDR'),
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );

            if ($ip !== false) {
                return $ip;
            }
        }

        return '';
    }

    /**
     * Attempt to get the IP address for a proxied client.
     *
     * @link http://tools.ietf.org/html/draft-ietf-appsawg-http-forwarded-10#section-5.2
     *
     * @return string|null
     */
    private function getIpAddressFromProxy(): ?string
    {
        if (! $this->serverRequest->hasHeader('HTTP_X_FORWARDED_FOR')) {
            return null;
        }

        // Extract IPs
        $ips = explode(',', $this->serverRequest->getHeader('HTTP_X_FORWARDED_FOR'));
        $ips = array_map('trim', $ips);

        // @codeCoverageIgnoreStart
        if (count($ips) === 0) {
            return null;
        }
        // @codeCoverageIgnoreEnd

        // The right-most address represents the first IP we do not know about
        // -- i.e., we do not know if it is a proxy server, or a client. As such,
        // we treat it as the originating IP.
        // @see http://en.wikipedia.org/wiki/X-Forwarded-For
        if ($ip = filter_var($ips[0], FILTER_VALIDATE_IP)) {
            return $ip;
        }

        // @codeCoverageIgnoreStart
        return null;
        // @codeCoverageIgnoreEnd
    }
}
