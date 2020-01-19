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

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use Viserio\Contract\Http\Exception\InvalidArgumentException;

class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * The request attributes.
     *
     * @var array<int|string, mixed>
     */
    private array $attributes = [];

    /**
     * The request cookies params.
     *
     * @var array<int|string, mixed>
     */
    private array $cookieParams = [];

    /** @var null|array<int|string, mixed>|object */
    private $parsedBody;

    /**
     * The request query string params.
     *
     * @var array<int|string, mixed>
     */
    private array $queryParams = [];

    /**
     * The server environment variables at the time the request was created.
     *
     * @var array<int|string, mixed>
     */
    private array $serverParams;

    /**
     * List of uploaded files.
     *
     * @var \Psr\Http\Message\UploadedFileInterface[]
     */
    private array $uploadedFiles = [];

    /**
     * Create a new server request instance.
     *
     * @param null|\Psr\Http\Message\UriInterface|string             $uri          uRI for the request
     * @param null|string                                            $method       hTTP method for the request
     * @param array<int|string, mixed>                               $headers      headers for the message
     * @param null|\Psr\Http\Message\StreamInterface|resource|string $body         message body
     * @param string                                                 $version      hTTP protocol version
     * @param array<int|string, mixed>                               $serverParams Typically the $_SERVER superglobal
     */
    public function __construct(
        $uri,
        $method = 'GET',
        array $headers = [],
        $body = null,
        string $version = '1.1',
        array $serverParams = []
    ) {
        $this->serverParams = $serverParams;

        parent::__construct($uri, $method, $headers, $body, $version);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookieParams(): array
    {
        return $this->cookieParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryParams(): array
    {
        return $this->queryParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getServerParams(): array
    {
        return $this->serverParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getUploadedFiles(): array
    {
        return $this->uploadedFiles;
    }

    /**
     * {@inheritdoc}
     */
    public function withUploadedFiles(array $uploadedFiles): ServerRequestInterface
    {
        $this->validateUploadedFiles($uploadedFiles);

        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withCookieParams(array $cookies): ServerRequestInterface
    {
        $new = clone $this;
        $new->cookieParams = $cookies;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withQueryParams(array $query): ServerRequestInterface
    {
        $new = clone $this;
        $new->queryParams = $query;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withParsedBody($data): ServerRequestInterface
    {
        if (! \is_array($data) && ! \is_object($data) && $data !== null) {
            throw new InvalidArgumentException(\sprintf('%s expects a null, array, or object argument; received [%s].', __METHOD__, \gettype($data)));
        }

        $new = clone $this;
        $new->parsedBody = $data;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($attribute, $default = null)
    {
        if (! \array_key_exists($attribute, $this->attributes)) {
            return $default;
        }

        return $this->attributes[$attribute];
    }

    /**
     * {@inheritdoc}
     */
    public function withAttribute($attribute, $value): ServerRequestInterface
    {
        $new = clone $this;
        $new->attributes[$attribute] = $value;

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutAttribute($attribute): ServerRequestInterface
    {
        if (! \array_key_exists($attribute, $this->attributes)) {
            return $this;
        }

        $new = clone $this;
        unset($new->attributes[$attribute]);

        return $new;
    }

    /**
     * Recursively validate the structure in an uploaded files array.
     *
     * @param array<int|string, mixed> $uploadedFiles
     *
     * @throws \Viserio\Contract\Http\Exception\InvalidArgumentException if any leaf is not an UploadedFileInterface instance
     */
    private function validateUploadedFiles(array $uploadedFiles): void
    {
        foreach ($uploadedFiles as $file) {
            if (\is_array($file)) {
                $this->validateUploadedFiles($file);

                continue;
            }

            if (! $file instanceof UploadedFileInterface) {
                throw new InvalidArgumentException('Invalid leaf in uploaded files structure.');
            }
        }
    }
}
