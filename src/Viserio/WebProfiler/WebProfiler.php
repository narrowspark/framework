<?php
declare(strict_types=1);
namespace Viserio\WebProfiler;

use DebugBar\DebugBar;
use DebugBar\Storage\PdoStorage;
use DebugBar\Storage\RedisStorage;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Routing\UrlGenerator as UrlGeneratorContract;

class WebProfiler extends DebugBar
{
    /**
     * Stream factory instance.
     *
     * @var \Interop\Http\Factory\StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * Url generator instance.
     *
     * @var \Viserio\Contracts\Routing\UrlGenerator
     */
    protected $urlGenerator;

    /**
     * Set a url generator instance.
     *
     * @param \Viserio\Contracts\Routing\UrlGenerator $urlGenerator
     */
    public function setUrlGenerator(UrlGeneratorContract $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;

        return $this;
    }

    /**
     * Get a url generator instance.
     *
     * @return \Viserio\Contracts\Routing\UrlGenerator|null
     */
    public function getUrlGenerator()
    {
        return $this->urlGenerator;
    }

    /**
     * Set stream factory instance.
     *
     * @param \Interop\Http\Factory\StreamFactoryInterface $factory
     */
    public function setStreamFactory(StreamFactoryInterface $factory)
    {
        $this->streamFactory = $factory;

        return $this;
    }

    /**
     * Get stream factory instance.
     *
     * @return \Interop\Http\Factory\StreamFactoryInterface
     */
    public function getStreamFactory(): StreamFactoryInterface
    {
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
     * Returns a JavascriptRenderer for this instance.
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
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @link https://github.com/symfony/WebProfilerBundle/blob/master/EventListener/WebDebugToolbarListener.php
     */
    public function injectWebProfiler(ResponseInterface $response): ResponseInterface
    {
        $content = (string) $response->getBody();
        $renderer = $this->getJavascriptRenderer();

        // if ($this->getStorage()) {
            // $openHandlerUrl = $this->urlGenerator->route('webprofiler.openhandler');

            // $renderer->setOpenHandlerUrl($openHandlerUrl);
        // }

        $renderedContent = $renderer->renderHead() . $renderer->render();

        $pos = strripos($content, '</body>');

        if ($pos !== false) {
            $stream = $this->getStreamFactory()->createStream(
                substr($content, 0, $pos) . $renderedContent . substr($content, $pos)
            );

            // Update the new content and reset the content length
            $response = $response->withoutHeader('Content-Length');

            return $response->withBody($stream);
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
