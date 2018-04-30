<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Response;

use Psr\Http\Message\UriInterface;
use Viserio\Component\Contract\Http\Exception\UnexpectedValueException;
use Viserio\Component\Http\Response;
use Viserio\Component\Http\Stream;

class RedirectResponse extends Response
{
    /**
     * Create a redirect response.
     *
     * Produces a redirect response with a Location header and the given status
     * (302 by default).
     *
     * Note: this method overwrites the `location` $headers value.
     *
     * @param string|UriInterface $uri     uRI for the Location header
     * @param int                 $status  integer status code for the redirect; 302 by default
     * @param array               $headers array of headers to use at initialization
     * @param string              $version protocol version
     *
     * @throws \Viserio\Component\Contract\Http\Exception\UnexpectedValueException
     */
    public function __construct($uri, int $status = 302, array $headers = [], string $version = '1.1')
    {
        if (! \is_string($uri) && ! $uri instanceof UriInterface) {
            throw new UnexpectedValueException(\sprintf(
                'Uri provided to %s MUST be a string or Psr\Http\Message\UriInterface instance; received [%s]',
                __CLASS__,
                (\is_object($uri) ? \get_class($uri) : \gettype($uri))
            ));
        }

        $headers['location'] = [(string) $uri];

        parent::__construct($status, $headers, new Stream(\fopen('php://temp', 'rb+')), $version);
    }
}
