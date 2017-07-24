<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Http;

use Psr\Http\Message\ServerRequestInterface;

final class ClientIp
{
    /**
     * A server request instance.
     *
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private $serverRequest;

    /**
     * List of proxy headers inspected for the client IP address.
     *
     * @var array
     */
    private static $headersToInspect = [
        'Forwarded',
        'X-Forwarded-For',
        'X-Forwarded',
        'X-Cluster-Client-Ip',
        'Client-Ip',
    ];

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
     * @return null|string
     */
    public function getIpAddress(): ?string
    {
        $ipAddress    = null;
        $request      = $this->serverRequest;
        $serverParams = $request->getServerParams();

        // direct IP address
        if (isset($serverParams['REMOTE_ADDR']) && $this->isValidIpAddress($serverParams['REMOTE_ADDR'])) {
            $ipAddress = $serverParams['REMOTE_ADDR'];
        }

        foreach (self::$headersToInspect as $header) {
            if ($request->hasHeader($header)) {
                $ip = $this->getFirstIpAddressFromHeader($request, $header);

                if ($this->isValidIpAddress($ip)) {
                    $ipAddress = $ip;

                    break;
                }
            }
        }

        return $ipAddress;
    }

    /**
     * Check that a given string is a valid IP address.
     *
     * @param string $ip
     *
     * @return bool
     */
    private function isValidIpAddress(string $ip): bool
    {
        return (bool) \filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6);
    }

    /**
     * Find out the client's IP address from the headers available to us.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     * @param string                                   $header        Header name
     *
     * @return string
     */
    private function getFirstIpAddressFromHeader(ServerRequestInterface $serverRequest, string $header): string
    {
        $items       = \explode(',', $serverRequest->getHeaderLine($header));
        $headerValue = \trim(\reset($items));

        if (\ucfirst($header) == 'Forwarded') {
            foreach (\explode(';', $headerValue) as $headerPart) {
                if (\mb_strtolower(\mb_substr($headerPart, 0, 4)) == 'for=') {
                    $for         = \explode(']', $headerPart);
                    $headerValue = \trim(\mb_substr(\reset($for), 4), " \t\n\r\0\x0B" . '"[]');

                    break;
                }
            }
        }

        return $headerValue;
    }
}
