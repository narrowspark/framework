<?php
declare(strict_types=1);
namespace Viserio\WebProfiler;

use DebugBar\DebugBar;
use DebugBar\Storage\PdoStorage;
use DebugBar\Storage\RedisStorage;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Viserio\HttpFactory\StreamFactory;

class WebProfiler extends DebugBar
{
    /**
     * [setStreamFactory description]
     *
     * @param \Psr\Http\Message\StreamInterface $factory
     */
    public function setStreamFactory(StreamInterface $factory)
    {
        return $this->streamFactory;
    }

    /**
     * [getStreamFactory description]
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    public function getStreamFactory(): StreamInterface
    {
        if ($this->streamFactory !== null) {
            return (new StreamFactory())->createStream();
        }

        return $this->streamFactory;
    }

    /**
     * Modify the response and inject the debugbar (or data in headers)
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function modifyResponse(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface {
        return $this->injectWebProfiler($response);
    }

    /**
     * Returns a JavascriptRenderer for this instance
     *
     * @param string $baseUrl
     * @param string $basePathng
     *
     * @return \Viserio\WebProfiler\JavascriptRenderer
     */
    public function getJavascriptRenderer($baseUrl = null, $basePath = null): JavascriptRenderer
    {
        if ($this->jsRenderer === null) {
            $this->jsRenderer = new JavascriptRenderer($this, $baseUrl, $basePath);
        }

        return $this->jsRenderer;
    }

    /**
     * Injects the web debug toolbar into the given Response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *                                                      Based on https://github.com/symfony/WebProfilerBundle/blob/master/EventListener/WebDebugToolbarListener.php
     */
    public function injectWebProfiler(ResponseInterface $response)
    {
        $content = (string) $response->getBody();
        $renderer = $this->getJavascriptRenderer();

        // if ($this->getStorage()) {
        //     $openHandlerUrl = route('debugbar.openhandler');

        //     $renderer->setOpenHandlerUrl($openHandlerUrl);
        // }

        $renderedContent = $renderer->renderHead() . $renderer->render();

        $pos = strripos($content, '</body>');

        if ($pos !== false) {
            $body = $this->getStreamFactory();
            $body->write(substr($content, 0, $pos) . $renderedContent . substr($content, $pos));

            // Update the new content and reset the content length
            $response = $response->withoutHeader('Content-Length');

            return $response->withBody($body);
        }

        $response->getBody()->write($renderedContent);

        return $response;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return bool
     */
    private function isHtmlResponse(ResponseInterface $response): bool
    {
        return $this->hasHeaderContains($response, 'Content-Type', 'text/html');
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return bool
     */
    private function isHtmlAccepted(ServerRequestInterface $request): bool
    {
        return $this->hasHeaderContains($request, 'Accept', 'text/html');
    }

    /**
     * @param \Psr\Http\Message\MessageInterface $message
     * @param string                             $headerName
     * @param string                             $value
     *
     * @return bool
     */
    private function hasHeaderContains(MessageInterface $message, string $headerName, string $value): bool
    {
        return strpos($message->getHeaderLine($headerName), $value) !== false;
    }

    /**
     * Returns a boolean TRUE for if the response has redirect status code.
     *
     * Five common HTTP status codes indicates a redirection beginning from 301.
     * 304 not modified and 305 use proxy are not redirects.
     *
     * @see https://en.wikipedia.org/wiki/List_of_HTTP_status_codes#3xx_Redirection
     *
     * @param \Psr\Http\Message\ResponseInterface $message
     *
     * @return bool
     */
    private function isRedirect(ResponseInterface $response): bool
    {
        return in_array($response->getStatusCode(), [301, 302, 303, 307, 308]);
    }
}
