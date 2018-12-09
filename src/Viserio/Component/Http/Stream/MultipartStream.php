<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Stream;

use Narrowspark\MimeType\MimeType;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Component\Http\Util;

/**
 * Stream that when read returns bytes for a streaming multipart or
 * multipart/form-data stream.
 */
class MultipartStream extends AbstractStreamDecorator
{
    private $boundary;

    /**
     * @param array  $elements array of associative arrays, each containing a
     *                         required "name" key mapping to the form field,
     *                         name, a required "contents" key mapping to a
     *                         StreamInterface/resource/string, an optional
     *                         "headers" associative array of custom headers,
     *                         and an optional "filename" key mapping to a
     *                         string to send as the filename in the part
     * @param string $boundary You can optionally provide a specific boundary
     *
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException
     */
    public function __construct(array $elements = [], $boundary = null)
    {
        $this->boundary = $boundary ?: \sha1(\uniqid('', true));

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
     * @param array $elements
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
     * @param array $headers
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
     * @param AppendStream $stream
     * @param array        $element
     *
     * @return void
     */
    private function addElement(AppendStream $stream, array $element): void
    {
        foreach (['contents', 'name'] as $key) {
            if (! \array_key_exists($key, $element)) {
                throw new InvalidArgumentException(\sprintf('A [%s] key is required', $key));
            }
        }

        $element['contents'] = Util::createStreamFor($element['contents']);

        if (empty($element['filename'])) {
            $uri = $element['contents']->getMetadata('uri');

            if (\substr($uri, 0, 6) !== 'php://') {
                $element['filename'] = $uri;
            }
        }

        [$body, $headers] = $this->createElement(
            $element['name'],
            $element['contents'],
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
     * @param array                             $headers
     *
     * @return array
     */
    private function createElement(string $name, StreamInterface $stream, ?string $filename, array $headers): array
    {
        // Set a default content-disposition header if one was no provided
        $disposition = $this->getHeader($headers, 'content-disposition');

        if (! $disposition) {
            $headers['Content-Disposition'] = ($filename === '0' || $filename)
                ? \sprintf(
                    'form-data; name="%s"; filename="%s"',
                    $name,
                    \basename($filename)
                )
                : "form-data; name=\"{$name}\"";
        }

        // Set a default content-length header if one was no provided
        $length = $this->getHeader($headers, 'content-length');

        if (! $length) {
            if ($length = $stream->getSize()) {
                $headers['Content-Length'] = (string) $length;
            }
        }

        // Set a default Content-Type if one was not supplied
        $type = $this->getHeader($headers, 'content-type');

        if (! $type && ($filename === '0' || $filename)) {
            if ($type = MimeType::guess($filename)) {
                $headers['Content-Type'] = $type;
            }
        }

        return [$stream, $headers];
    }

    private function getHeader(array $headers, $key)
    {
        $lowercaseHeader = \strtolower($key);

        foreach ($headers as $k => $v) {
            if (\strtolower($k) === $lowercaseHeader) {
                return $v;
            }
        }

        return null;
    }
}
