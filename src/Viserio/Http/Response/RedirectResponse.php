<?php
declare(strict_types=1);
namespace Viserio\Http\Response;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;
use Viserio\Http\Response;
use Viserio\Http\StreamFactory;

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
     * @param string|UriInterface $uri     URI for the Location header.
     * @param int                 $status  Integer status code for the redirect; 302 by default.
     * @param array               $headers Array of headers to use at initialization.
     */
    public function __construct($uri, int $status = 302, array $headers = [])
    {
        if (! is_string($uri) && ! $uri instanceof UriInterface) {
            throw new InvalidArgumentException(sprintf(
                'Uri provided to %s MUST be a string or Psr\Http\Message\UriInterface instance; received "%s"',
                __CLASS__,
                (is_object($uri) ? get_class($uri) : gettype($uri))
            ));
        }

        $headers['location'] = [(string) $uri];

        parent::__construct($status, $headers, (new StreamFactory)->createStream());
    }
}
