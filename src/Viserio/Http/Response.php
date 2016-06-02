<?php
namespace Viserio\Http;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response extends AbstractMessage implements ResponseInterface
{
    /**
     * @param string|resource|StreamInterface $body    Stream identifier and/or actual stream resource
     * @param int                             $status  Status code for the response, if any.
     * @param array                           $headers Headers for the response, if any.
     *
     * @throws InvalidArgumentException on any invalid element.
     */
    public function __construct($body = 'php://memory', int $status = 200, array $headers = [])
    {
        $this->setStatusCode($status);
        $this->stream = $this->getStream($body, 'wb+');
        list($this->headerNames, $headers) = $this->filterHeaders($headers);
        $this->assertHeaders($headers);
        $this->headers = $headers;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase()
    {
        if (! $this->reasonPhrase && isset($this->phrases[$this->statusCode])) {
            $this->reasonPhrase = $this->phrases[$this->statusCode];
        }

        return $this->reasonPhrase;
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $new = clone $this;
        $new->setStatusCode($code);
        $new->reasonPhrase = $reasonPhrase;

        return $new;
    }
    /**
     * Validate a status code.
     *
     * @param int|string $code
     * @throws InvalidArgumentException on an invalid status code.
     */
    private function setStatusCode($code)
    {
        if (! is_numeric($code)
            || is_float($code)
            || $code < 100
            || $code >= 600
        ) {
            throw new InvalidArgumentException(sprintf(
                'Invalid status code "%s"; must be an integer between 100 and 599, inclusive',
                (is_scalar($code) ? $code : gettype($code))
            ));
        }
        $this->statusCode = $code;
    }
}
