<?php
declare(strict_types=1);
namespace Viserio\WebProfiler;

use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Viserio\Contracts\Cache\Traits\CacheItemPoolAwareTrait;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Contracts\Log\Traits\LoggerAwareTrait;
use Viserio\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Contracts\WebProfiler\DataCollector as DataCollectorContract;
use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;

class WebProfiler implements WebProfilerContract
{
    use CacheItemPoolAwareTrait;
    use EventsAwareTrait;
    use LoggerAwareTrait;

    /**
     * All registered data collectors.
     *
     * @var array
     */
    protected $collectors = [];

    /**
     * A server request instance.
     *
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $serverRequest;

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
     * Url generator instance.
     *
     * @var \Viserio\WebProfiler\AssetsRenderer
     */
    protected $assetsRenderer;

    /**
     * Enables or disables the profiler.
     *
     * @var bool
     */
    protected $enabled = false;

    /**
     * Template for webprofiler.
     *
     * @var string
     */
    protected $template = __DIR__ . '/Resources/views/webprofiler.html.php';

    /**
     * Create new webprofiler instance.
     *
     * @param \Viserio\WebProfiler\AssetsRenderer $assetsRenderer
     */
    public function __construct($assetsRenderer)
    {
        $this->assetsRenderer = $assetsRenderer->setWebProfiler($this);
    }

    /**
     * Disables the profiler.
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * Enables the profiler.
     */
    public function enable()
    {
        $this->enabled = true;
    }

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
    public function getUrlGenerator(): ?UrlGenerator
    {
        return $this->urlGenerator;
    }

    /**
     * Set stream factory instance.
     *
     * @param \Interop\Http\Factory\StreamFactoryInterface $factory
     *
     * @return \Viserio\Contracts\WebProfiler\WebProfiler
     */
    public function setStreamFactory(StreamFactoryInterface $factory): WebProfiler
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
     * Set the webprofiler template path.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setTemplate(string $path): WebProfilerContract
    {
        $this->template = $path;

        return $this;
    }

    /**
     * Get the webprofiler template path.
     *
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * Adds a data collector.
     *
     * @param \Viserio\Contracts\WebProfiler\DataCollector $collector
     *
     * @throws \RuntimeException
     *
     * @return $this
     */
    public function addCollector(DataCollectorContract $collector): WebProfilerContract
    {
        if ($collector->getName() === '__meta') {
            throw new RuntimeException('"__meta" is a reserved name and cannot be used as a collector name');
        }

        if (isset($this->collectors[$collector->getName()])) {
            throw new RuntimeException(sprintf('[%s] is already a registered collector', $collector->getName()));
        }

        $this->collectors[$collector->getName()] = $collector;

        return $this;
    }

    /**
     * Checks if a data collector has been added.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasCollector(string $name): bool
    {
        return isset($this->collectors[$name]);
    }

    /**
     * Returns an array of all data collectors.
     *
     * @return array
     */
    public function getCollectors(): array
    {
        return $this->collectors;
    }

    /**
     * Modify the response and inject the debugbar.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function modifyResponse(
        ServerRequestInterface $serverRequest,
        ResponseInterface $response
    ): ResponseInterface {
        if ($this->runningInConsole()) {
            return $response;
        }

        foreach ($this->collectors as $name => $collector) {
            $collector->collect($serverRequest, $response);

            $this->collectors[$name] = $collector;
        }

        return $this->injectWebProfiler($response);
    }

    /**
     * Returns a AssetsRenderer for this instance.
     *
     * @return \Viserio\WebProfiler\AssetsRenderer
     */
    public function getAssetsRenderer(): AssetsRenderer
    {
        return $this->assetsRenderer;
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
    protected function injectWebProfiler(ResponseInterface $response): ResponseInterface
    {
        $content = (string) $response->getBody();

        $assets = $this->getAssetsRenderer();
        $template = new TemplateManager(
            $this->collectors,
            $this->template,
            $assets->getIcons()
        );

        $renderedContent = $assets->render() . $template->render();

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
     * {@inheritdoc}
     */
    private function runningInConsole(): bool
    {
        return substr(PHP_SAPI, 0, 3) === 'cgi';
    }

    /**
     * Check if response is a html response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return bool
     */
    private function isHtmlResponse(ResponseInterface $response): bool
    {
        return $this->hasHeaderContains($response, 'Content-Type', 'text/html');
    }

    /**
     * Check if request accept html.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return bool
     */
    private function isHtmlAccepted(ServerRequestInterface $request): bool
    {
        return $this->hasHeaderContains($request, 'Accept', 'text/html');
    }

    /**
     * Checks if headers contains searched header.
     *
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
