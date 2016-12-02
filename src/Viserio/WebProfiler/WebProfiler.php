<?php
declare(strict_types=1);
namespace Viserio\WebProfiler;

use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\PhpInfoCollector;
use DebugBar\DataCollector\TimeDataCollector;
use DebugBar\DebugBar;
use DebugBar\Storage\PdoStorage;
use DebugBar\Storage\RedisStorage;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Config\Manager as ConfigManagerContract;
use Viserio\Contracts\Config\Traits\ConfigAwareTrait;
use Viserio\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Foundation\Application;
use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;

class WebProfiler extends DebugBar implements WebProfilerContract
{
    use ConfigAwareTrait;

    /**
     * Normalized Version.
     *
     * @var string
     */
    protected $version;

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
     * @param \Viserio\Config\Manager                  $config
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function __construct(ConfigManagerContract $config, ServerRequestInterface $serverRequset)
    {
        $this->config = $config;
        $this->serverRequset = $serverRequset;

        $version = class_exists(Application::class) ? Application::VERSION : 0;

        $this->version = $config->get('webprofiler.version', $version);
    }

    /**
     * Enable the Debugbar and boot, if not already booted.
     */
    public function enable()
    {
        $this->enabled = true;

        if (!$this->booted) {
            $this->boot();
        }
    }

    /**
     * Check if the Debugbar is enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->config->get('webprofiler.enabled', $this->enabled);
    }

    public function boot()
    {
        $webprofiler = $this;

        if ($this->shouldCollect('phpinfo', true)) {
            $this->addCollector(new PhpInfoCollector());
        }

        if ($this->shouldCollect('messages', true)) {
            $this->addCollector(new MessagesCollector());
        }

        if ($this->shouldCollect('time', true)) {
            $this->addCollector(new TimeDataCollector());
        }

        if ($this->shouldCollect('memory', true)) {
            $this->addCollector(new MemoryCollector());
        }
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
                // 'ip' => $request->getClientIp()
            ],
        ];

        foreach ($this->collectors as $name => $collector) {
            $this->data[$name] = $collector->collect();
        }

        // Remove all invalid (non UTF-8) characters
        array_walk_recursive(
            $this->data,
            function (&$item) {
                if (is_string($item) && !mb_check_encoding($item, 'UTF-8')) {
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
        if ($this->runningInConsole() || ! $this->isEnabled()) {
            return $response;
        }

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
    protected function injectWebProfiler(ResponseInterface $response): ResponseInterface
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

    /**
     * Check if a collector should be activated.
     *
     * @param string $name
     * @param bool   $default
     *
     * @return bool
     */
    private function shouldCollect(string $name, bool $default = false): bool
    {
        return $this->config->get('webprofiler.collectors.' . $name, $default);
    }
}
