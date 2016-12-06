<?php
declare(strict_types=1);
namespace Viserio\WebProfiler;

use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Contracts\WebProfiler\DataCollector as DataCollectorContract;
use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;

class WebProfiler implements WebProfilerContract
{
    use EventsAwareTrait;

    /**
     * All registered data collectors.
     *
     * @var array
     */
    protected $collectors = [];

    /**
     * A ServerRequest instance.
     *
     * @var string
     */
    protected $serverRequset;

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
     * True when booted.
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * True when enabled, false for disabled.
     *
     * @var bool
     */
    protected $enabled = false;

    /**
     * Create a new web profiler instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function __construct(
        ServerRequestInterface $serverRequset
    ) {
        $this->serverRequset = $serverRequset;
        $this->templatePath = __DIR__ . '/Resources/views/webprofiler.html.php';
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
     * Set the webprofiler template path.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setTemplate(string $path): WebProfilerContract
    {
        $this->templatePath = $path;

        return $this;
    }

    /**
     * Get the webprofiler template path.
     *
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->templatePath;
    }

    /**
     * Adds a data collector
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
            throw new RuntimeException("'__meta' is a reserved name and cannot be used as a collector name");
        }

        if (isset($this->collectors[$collector->getName()])) {
            throw new RuntimeException(sprintf('[%s] is already a registered collector', $collector->getName()));
        }

        $this->collectors[$collector->getName()] = $collector;

        return $this;
    }

    /**
     * Returns an array of all data collectors
     *
     * @return array
     */
    public function getCollectors(): array
    {
        return $this->collectors;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        $request = $this->serverRequset;

        $this->data = [
            '__meta' => [
                'id' => $this->getCurrentRequestId(),
                'datetime' => date('Y-m-d H:i:s'),
                'utime' => microtime(true),
                'method' => $request->getMethod(),
                'uri' => (string) $request->getUri(),
                'ip' => '',
            ],
        ];

        foreach ($this->collectors as $name => $collector) {
            $this->data[$name] = $collector->collect();
        }

        // Remove all invalid (non UTF-8) characters
        array_walk_recursive(
            $this->data,
            function (&$item) {
                if (is_string($item) && ! mb_check_encoding($item, 'UTF-8')) {
                    $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
                }
            }
        );

        if ($this->storage !== null) {
            $this->storage->save($this->getCurrentRequestId(), $this->data);
        }

        return $this->data;
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
        if ($this->runningInConsole()) {
            return $response;
        }

        return $this->injectWebProfiler($response);
    }

    /**
     * Returns a AssetsRenderer for this instance.
     *
     * @param string $baseUrl
     * @param string $basePathng
     *
     * @return \Viserio\WebProfiler\AssetsRenderer
     */
    public function getAssetsRenderer($baseUrl = null, $basePath = null): AssetsRenderer
    {
        if ($this->assetsRenderer === null) {
            $this->assetsRenderer = new AssetsRenderer($this, $baseUrl, $basePath);
        }

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

        $template = new TemplateManager($this->collectors, $this->templatePath);

        $renderedContent = $this->getAssetsRenderer()->render() . $template->render();

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
