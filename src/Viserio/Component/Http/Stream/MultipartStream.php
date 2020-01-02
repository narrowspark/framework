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

namespace Viserio\Component\Http\Stream;

use Narrowspark\MimeType\MimeType;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Util;
use Viserio\Contract\Http\Exception\InvalidArgumentException;

/**
 * Stream that when read returns bytes for a streaming multipart or
 * multipart/form-data stream.
 */
class MultipartStream extends AbstractStreamDecorator
{
    /** @var string */
    protected const UPPER = '_ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /** @var string */
    protected const LOWER = '-abcdefghijklmnopqrstuvwxyz';

    /** @var string */
    private $boundary;

    /**
     * @param array<int|string, mixed> $elements array of associative arrays, each containing a
     *                                           required "name" key mapping to the form field,
     *                                           name, a required "contents" key mapping to a
     *                                           StreamInterface/resource/string, an optional
     *                                           "headers" associative array of custom headers,
     *                                           and an optional "filename" key mapping to a
     *                                           string to send as the filename in the part
     * @param string                   $boundary You can optionally provide a specific boundary
     *
     * @throws \Viserio\Contract\Http\Exception\InvalidArgumentException
     */
    public function __construct(array $elements = [], ?string $boundary = null)
    {
        $this->boundary = $boundary ?? \sha1(\uniqid('', true));

        parent::__construct($this->createAppendStream($elements));
    }

    /**
     * Get the boundary.
     *
     * @return string
     */
    public function getBoundary(): string
    {
        return $this->boundary;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        return false;
    }

    /**
     * Create the aggregate stream that will be used to upload the POST data.
     *
     * @param array<int|string, mixed> $elements
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    private function createAppendStream(array $elements): StreamInterface
    {
        $stream = new AppendStream();

        foreach ($elements as $element) {
            $this->addElement($stream, $element);
        }

        // Add the trailing boundary with CRLF
        $stream->addStream(Util::createStreamFor("--{$this->boundary}--\r\n"));

        return $stream;
    }

    /**
     * Get the headers needed before transferring the content of a POST file.
     *
     * @param array<int|string, int|string> $headers
     *
     * @return string
     */
    private function getHeaders(array $headers): string
    {
        $str = '';

        foreach ($headers as $key => $value) {
            $str .= "{$key}: {$value}\r\n";
        }

        return "--{$this->boundary}\r\n" . \trim($str) . "\r\n\r\n";
    }

    /**
     * @param \Viserio\Component\Http\Stream\AppendStream $stream
     * @param array<int|string, mixed>                    $element
     *
     * @return void
     */
    private function addElement(AppendStream $stream, array $element): void
    {
        foreach (['contents', 'name'] as $key) {
            if (! \array_key_exists($key, $element)) {
                throw new InvalidArgumentException(\sprintf('A [%s] key is required.', $key));
            }
        }

        $element['contents'] = Util::createStreamFor($element['contents']);

        if (! \array_key_exists('filename', $element)) {
            $uri = $element['contents']->getMetadata('uri');

            if (\strpos($uri, 'php://') !== 0) {
                $element['filename'] = $uri;
            }
        }

        /** @var \Psr\Http\Message\StreamInterface $body */
        $body = $element['contents'];

        $headers = $this->createElement(
            $element['name'],
            $body,
            $element['filename'] ?? null,
            $element['headers'] ?? []
        );

        $stream->addStream(Util::createStreamFor($this->getHeaders($headers)));
        $stream->addStream($body);
        $stream->addStream(Util::createStreamFor("\r\n"));
    }

    /**
     * @param string                            $name
     * @param \Psr\Http\Message\StreamInterface $stream
     * @param null|string                       $filename
     * @param array<int|string, mixed>          $headers
     *
     * @return array<int|string, mixed>
     */
    private function createElement(string $name, StreamInterface $stream, ?string $filename, array $headers): array
    {
        // Set a default content-disposition header if one was no provided
        $disposition = $this->getHeader($headers, 'content-disposition');

        if ($disposition === null) {
            if ($filename === '0' || \is_string($filename)) {
                $contentDisposition = \sprintf(
                    'form-data; name="%s"; filename="%s"',
                    $name,
                    self::basename($filename)
                );
            } else {
                $contentDisposition = "form-data; name=\"{$name}\"";
            }

            $headers['Content-Disposition'] = $contentDisposition;
        }

        // Set a default content-length header if one was no provided
        $length = $this->getHeader($headers, 'content-length');

        if ($length === null || $length === '0') {
            if (($length = $stream->getSize()) > 0) {
                $headers['Content-Length'] = (string) $length;
            }
        }

        // Set a default Content-Type if one was not supplied
        $type = $this->getHeader($headers, 'content-type');

        if ($type === null && ($filename === '0' || \is_string($filename))) {
            if (null !== $type = MimeType::guess($filename)) {
                $headers['Content-Type'] = $type;
            }
        }

        return $headers;
    }

    /**
     * @param array<int|string, mixed> $headers
     * @param int|string               $key
     *
     * @return mixed
     */
    private function getHeader(array $headers, $key)
    {
        $lowercaseHeader = \strtr((string) $key, self::UPPER, self::LOWER);

        foreach ($headers as $k => $v) {
            if (\strtr((string) $k, self::UPPER, self::LOWER) === $lowercaseHeader) {
                return $v;
            }
        }

        return null;
    }

    /**
     * Gets the filename from a given path.
     *
     * PHP's basename() does not properly support streams or filenames beginning with a non-US-ASCII character.
     *
     * @author Drupal 8.2
     *
     * @param string $path
     *
     * @return string
     */
    private static function basename(string $path): string
    {
        $separators = '/';

        if (\DIRECTORY_SEPARATOR !== '/') {
            // For Windows OS add special separator.
            $separators .= \DIRECTORY_SEPARATOR;
        }

        // Remove right-most slashes when $path points to directory.
        $path = \rtrim($path, $separators);

        // Returns the trailing part of the $path starting after one of the directory separators.
        return \preg_match('@[^' . \preg_quote($separators, '@') . ']+$@', $path, $matches) === 1 ? $matches[0] : '';
    }
}
