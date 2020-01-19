<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Http;

use Fig\Http\Message\StatusCodeInterface;
use Narrowspark\HttpStatus\Exception\InvalidArgumentException as HttpStatusInvalidArgumentException;
use Narrowspark\HttpStatus\HttpStatus;
use Psr\Http\Message\ResponseInterface;
use Viserio\Contract\Http\Exception\InvalidArgumentException;

class Response extends AbstractMessage implements ResponseInterface, StatusCodeInterface
{
    private string $reasonPhrase = '';

    private int $statusCode;

    /**
     * Create a new response instance.
     *
     * @param int                                                    $status
     * @param array<int|string, mixed>                               $headers headers for the response, if any
     * @param null|\Psr\Http\Message\StreamInterface|resource|string $body    Stream identifier and/or actual stream resource
     * @param string                                                 $version protocol version
     *
     * @throws \Narrowspark\HttpStatus\Exception\InvalidArgumentException on any invalid status
     * @throws \Viserio\Contract\Http\Exception\InvalidArgumentException  on any invalid content
     */
    public function __construct(
        int $status = self::STATUS_OK,
        array $headers = [],
        $body = null,
        string $version = '1.1'
    ) {
        try {
            $this->statusCode = HttpStatus::filterStatusCode($status);
        } catch (HttpStatusInvalidArgumentException $exception) {
            throw new InvalidArgumentException($exception->getMessage(), $exception->getCode(), $exception);
        }

        $this->stream = null;

        if ($body !== '' && $body !== null) {
            $this->stream = Util::createStreamFor($body);
        }

        $this->setHeaders($headers);
        $this->protocolVersion = $version;
    }

    /**
     * {@inheritdoc}
     */
    public function getReasonPhrase(): string
    {
        if ($this->reasonPhrase === '') {
            try {
                $this->reasonPhrase = HttpStatus::getReasonPhrase($this->statusCode);
            } catch (\Narrowspark\HttpStatus\Exception\InvalidArgumentException $exception) {
                throw new InvalidArgumentException($exception->getMessage(), $exception->getCode(), $exception);
            }
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
        if (! \is_int($code)) {
            throw new InvalidArgumentException(\sprintf('Invalid type [%s] provided for $code.', \is_object($code) ? \get_class($code) : \gettype($code)));
        }

        $new = clone $this;

        try {
            $new->statusCode = HttpStatus::filterStatusCode($code);
        } catch (\Narrowspark\HttpStatus\Exception\InvalidArgumentException $exception) {
            throw new InvalidArgumentException($exception->getMessage(), $exception->getCode(), $exception);
        }

        if (! \is_string($reasonPhrase)) {
            throw new InvalidArgumentException(\sprintf('Unsupported response reason phrase; must be a string, received [%s].', \is_object($reasonPhrase) ? \get_class($reasonPhrase) : \gettype($reasonPhrase)));
        }

        $new->reasonPhrase = $reasonPhrase;

        return $new;
    }
}
