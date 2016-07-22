<?php
declare(strict_types=1);
namespace Viserio\Http\Response;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Viserio\Http\{
    Response,
    Stream
};
use Viserio\Http\Response\Traits\InjectContentTypeTrait;

class HtmlResponse extends Response
{
    use InjectContentTypeTrait;

    /**
     * Create an HTML response.
     *
     * Produces an HTML response with a Content-Type of text/html and a default
     * status of 200.
     *
     * @param string|StreamInterface $html HTML or stream for the message body.
     * @param int                    $status Integer status code for the response; 200 by default.
     * @param array                  $headers Array of headers to use at initialization.
     *
     * @throws InvalidArgumentException if $html is neither a string or stream.
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
     * @param string|StreamInterface $html
     *
     * @return StreamInterface
     *
     * @throws InvalidArgumentException if $html is neither a string or stream.
     */
    private function createBody($html): StreamInterface
    {
        if ($html instanceof StreamInterface) {
            return $html;
        }

        if (! is_string($html)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid content (%s) provided to %s',
                (is_object($html) ? get_class($html) : gettype($html)),
                __CLASS__
            ));
        }

        $body = new Stream(fopen('php://temp', 'wb+'));
        $body->write($html);
        $body->rewind();

        return $body;
    }
}
