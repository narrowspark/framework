<?php
declare(strict_types=1);
namespace Viserio\Foundation\Http;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Viserio\Contracts\Foundation\Emitter as EmitterContract;

class Emitter implements EmitterContract
{
    /**
     * {@inheritdoc}
     */
    public function emit(ResponseInterface $response)
    {
        if (headers_sent()) {
            throw new RuntimeException('Unable to emit response; headers already sent');
        }

        $response = $this->injectContentLength($response);

        $this->emitStatusLine($response);
        $this->emitHeaders($response);
        $this->terminateOutputBuffering(0);
        $this->emitBody($response);
        $this->cleanUp();
    }

    /**
     * Inject the Content-Length header if is not already present.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function injectContentLength(ResponseInterface $response): ResponseInterface
    {
        if (! $response->hasHeader('Content-Length')) {
            // PSR-7 indicates int OR null for the stream size; for null values,
            // we will not auto-inject the Content-Length.
            if (null !== $response->getBody()->getSize()) {
                return $response->withHeader('Content-Length', (string) $response->getBody()->getSize());
            }
        }

        return $response;
    }

    /**
     * Emit the status line.
     *
     * Emits the status line using the protocol version and status code from
     * the response; if a reason phrase is availble, it, too, is emitted.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    protected function emitStatusLine(ResponseInterface $response)
    {
        header(sprintf(
            'HTTP/%s %d %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        ));
    }

    /**
     * Emit response headers.
     *
     * Loops through each header, emitting each; if the header value
     * is an array with multiple values, ensures that each is sent
     * in such a way as to create aggregate headers (instead of replace
     * the previous).
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    protected function emitHeaders(ResponseInterface $response)
    {
        foreach ($response->getHeaders() as $header => $values) {
            $name  = $this->filterHeader($header);
            $first = true;

            foreach ($values as $value) {
                header(sprintf(
                    '%s: %s',
                    $name,
                    $value
                ), $first);

                $first = false;
            }
        }
    }

    /**
     * Emit the message body.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    protected function emitBody(ResponseInterface $response)
    {
        echo $response->getBody();
    }

    /**
     * Close response stream and terminate output buffering.
     *
     * @param int $maxBufferLevel
     */
    protected function terminateOutputBuffering(int $maxBufferLevel = 0)
    {
        // Command line output buffering is disabled in cli by default
        if (mb_substr(PHP_SAPI, 0, 3) === 'cgi') {
            return;
        }

        // avoid infinite loop on clearing
        // output buffer by set level to 0
        // if $maxBufferLevel is smaller
        if (-1 > $maxBufferLevel) {
            $maxBufferLevel = 0;
        }

        // terminate all output buffers until $maxBufferLevel is 0 or desired level
        while (ob_get_level() > $maxBufferLevel) {
            ob_end_clean();
        }
    }

    /**
     * Perform garbage collection.
     */
    protected function cleanUp()
    {
        // try to enable garbage collection
        if (! gc_enabled()) {
            @gc_enable();
        }
        // collect garbage only if garbage
        // collection is enabled
        if (gc_enabled()) {
            gc_collect_cycles();
        }
    }

    /**
     * Filter a header name to wordcase.
     *
     * @param string $header
     *
     * @return string
     */
    protected function filterHeader(string $header): string
    {
        $filtered = str_replace('-', ' ', $header);
        $filtered = ucwords($filtered);

        return str_replace(' ', '-', $filtered);
    }
}
