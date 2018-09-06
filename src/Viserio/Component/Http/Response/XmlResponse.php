<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Response;

use Psr\Http\Message\StreamInterface;
use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Component\Http\Response;
use Viserio\Component\Http\Response\Traits\InjectContentTypeTrait;
use Viserio\Component\Http\Stream;

class XmlResponse extends Response
{
    use InjectContentTypeTrait;

    /**
     * Create an XML response.
     *
     * Produces an XML response with a Content-Type of text/xml and a default status of 200.
     *
     * @param \Psr\Http\Message\StreamInterface|string $xml     plain text or stream for the message body
     * @param null|string                              $charset content charset; default is utf-8
     * @param int                                      $status  integer status code for the response; 200 by default
     * @param array                                    $headers array of headers to use at initialization
     * @param string                                   $version protocol version
     *
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException if $xml is neither a string or stream
     */
    public function __construct(
        $xml,
        ?string $charset = null,
        int $status      = self::STATUS_OK,
        array $headers   = [],
        string $version  = '1.1'
    ) {
        parent::__construct(
            $status,
            $this->injectContentType('text/xml; charset=' . ($charset ?? 'utf-8'), $headers),
            $this->createBody($xml),
            $version
        );
    }

    /**
     * Create the message body.
     *
     * @param \Psr\Http\Message\StreamInterface|string $text
     *
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException if $text is neither a string or stream
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    private function createBody($text): StreamInterface
    {
        if ($text instanceof StreamInterface) {
            return $text;
        }

        if (! \is_string($text)) {
            throw new InvalidArgumentException(\sprintf(
                'Invalid content [%s] provided to %s',
                (\is_object($text) ? \get_class($text) : \gettype($text)),
                __CLASS__
            ));
        }

        $body = new Stream(\fopen('php://temp', 'w+b'));
        $body->write($text);
        $body->rewind();

        return $body;
    }
}
