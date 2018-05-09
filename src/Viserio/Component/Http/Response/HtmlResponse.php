<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Response;

use Psr\Http\Message\StreamInterface;
use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Component\Http\Response;
use Viserio\Component\Http\Response\Traits\InjectContentTypeTrait;
use Viserio\Component\Http\Stream;

class HtmlResponse extends Response
{
    use InjectContentTypeTrait;

    /**
     * Create an HTML response.
     *
     * Produces an HTML response with a Content-Type of text/html and a default
     * status of 200.
     *
     * @param \Psr\Http\Message\StreamInterface|string $html    HTML or stream for the message body
     * @param null|string                              $charset content charset; default is utf-8
     * @param int                                      $status  integer status code for the response; 200 by default
     * @param array                                    $headers array of headers to use at initialization
     * @param string                                   $version protocol version
     *
     * @throws \Narrowspark\HttpStatus\Exception\InvalidArgumentException
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException if $html is neither a string or stream
     */
    public function __construct(
        $html,
        ?string $charset = null,
        int $status = self::STATUS_OK,
        array $headers = [],
        string $version = '1.1'
    ) {
        parent::__construct(
            $status,
            $this->injectContentType('text/html; charset=' . ($charset ?? 'utf-8'), $headers),
            $this->createBody($html),
            $version
        );
    }

    /**
     * Create the message body.
     *
     * @param \Psr\Http\Message\StreamInterface|string $html
     *
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException if $html is neither a string or stream
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    private function createBody($html): StreamInterface
    {
        if ($html instanceof StreamInterface) {
            return $html;
        }

        if (! \is_string($html)) {
            throw new InvalidArgumentException(\sprintf(
                'Invalid content [%s] provided to %s',
                (\is_object($html) ? \get_class($html) : \gettype($html)),
                __CLASS__
            ));
        }

        $body = new Stream(\fopen('php://temp', 'wb+'));
        $body->write($html);
        $body->rewind();

        return $body;
    }
}
