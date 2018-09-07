<?php
declare(strict_types=1);
namespace Viserio\Component\Http;

use Fig\Http\Message\StatusCodeInterface;
use Narrowspark\HttpStatus\HttpStatus;
use Psr\Http\Message\ResponseInterface;
use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;

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
     * @throws \Narrowspark\HttpStatus\Exception\InvalidArgumentException          on any invalid status
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException on any invalid content
     */
    public function __construct(
        int $status     = self::STATUS_OK,
        array $headers  = [],
        $body           = null,
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
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        if (! \is_numeric($code)
            || \is_float($code)
            || $code < HttpStatus::MINIMUM
            || $code > HttpStatus::MAXIMUM
        ) {
            throw new InvalidArgumentException(\sprintf(
                'Invalid status code [%s]; must be an integer between %d and %d, inclusive.',
                \is_scalar($code) ? $code : \gettype($code),
                HttpStatus::MINIMUM,
                HttpStatus::MAXIMUM
            ));
        }

        if (! \is_string($reasonPhrase)) {
            throw new InvalidArgumentException(\sprintf(
                'Unsupported response reason phrase; must be a string, received [%s]',
                \is_object($reasonPhrase) ? \get_class($reasonPhrase) : \gettype($reasonPhrase)
            ));
        }

        $new               = clone $this;
        $new->statusCode   = HttpStatus::filterStatusCode($code);
        $new->reasonPhrase = $reasonPhrase;

        return $new;
    }
}
