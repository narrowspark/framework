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
     * @param StreamInterface|string $html    hTML or stream for the message body
     * @param int                    $status  integer status code for the response; 200 by default
     * @param array                  $headers array of headers to use at initialization
     *
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException if $html is neither a string or stream
     */
    public function __construct($html, int $status = 200, array $headers = [])
    {
        parent::__construct(
            $status,
            $this->injectContentType('text/html; charset=utf-8', $headers),
            $this->createBody($html)
        );
    }

    /**
     * Create the message body.
     *
     * @param StreamInterface|string $html
     *
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException if $html is neither a string or stream
     *
     * @return StreamInterface
     */
    private function createBody($html): StreamInterface
    {
        if ($html instanceof StreamInterface) {
            return $html;
        }

        if (! \is_string($html)) {
            throw new InvalidArgumentException(\sprintf(
                'Invalid content (%s) provided to %s',
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
