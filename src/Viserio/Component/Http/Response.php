<?php
declare(strict_types=1);
namespace Viserio\Component\Http;

use Fig\Http\Message\StatusCodeInterface;
use Narrowspark\HttpStatus\HttpStatus;
use Psr\Http\Message\ResponseInterface;

class Response extends AbstractMessage implements ResponseInterface, StatusCodeInterface
{
    /**
     * @var string
     */
    private $reasonPhrase = '';

    /**
     * @var null|int
     */
    private $statusCode;

    /**
     * Create a new response instance.
     *
     * @param int                                                    $status  status code for the response, if any
     * @param array                                                  $headers headers for the response, if any
     * @param null|\Psr\Http\Message\StreamInterface|resource|string $body    Stream identifier and/or actual stream resource
     * @param string                                                 $version protocol version
     *
     * @throws \InvalidArgumentException on any invalid element
     */
    public function __construct(
        int $status = self::STATUS_OK,
        array $headers = [],
        $body = null,
        string $version = '1.1'
    ) {
        $this->statusCode = HttpStatus::filterStatusCode($status);

        if ($body !== '' && $body !== null) {
            $this->stream = $this->createStream($body);
        }

        $this->setHeaders($headers);
        $this->protocol = $version;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase(): string
    {
        if ($this->reasonPhrase === '') {
            $this->reasonPhrase = HttpStatus::getReasonPhrase($this->statusCode);
        }

        return $this->reasonPhrase;
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        $new               = clone $this;
        $new->statusCode   = HttpStatus::filterStatusCode($code);
        $new->reasonPhrase = $reasonPhrase;

        return $new;
    }

    /**
     * String representation of Response-object as HTTP message.
     *
     * @return string
     */
    public function __toString(): string
    {
        $response = $this;

        $msg = 'HTTP/' . $response->getProtocolVersion() . ' ' .
            $response->getStatusCode() . ' ' .
            $response->getReasonPhrase();

        if (! $response->hasHeader('Content-Length')) {
            try {
                $response = $response->withAddedHeader(
                    'Content-Length',
                    (string) $response->getBody()->getSize()
                );
            } catch (\Throwable $e) {
                return $e->getMessage();
            }
        }

        foreach ($response->getHeaders() as $name => $values) {
            $msg .= "\r\n{$name}: " . implode(', ', $values);
        }

        return "{$msg}\r\n\r\n" . $response->getBody();
    }
}
