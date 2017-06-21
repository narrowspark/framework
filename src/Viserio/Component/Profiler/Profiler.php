<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;
use Throwable;
use Viserio\Component\Contract\Cache\Traits\CacheItemPoolAwareTrait;
use Viserio\Component\Contract\Events\Traits\EventManagerAwareTrait;
use Viserio\Component\Contract\HttpFactory\Traits\StreamFactoryAwareTrait;
use Viserio\Component\Contract\Profiler\AssetsRenderer as AssetsRendererContract;
use Viserio\Component\Contract\Profiler\DataCollector as DataCollectorContract;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Contract\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Component\Support\Http\ClientIp;

class Profiler implements ProfilerContract, LoggerAwareInterface
{
    use CacheItemPoolAwareTrait;
    use EventManagerAwareTrait;
    use LoggerAwareTrait;
    use StreamFactoryAwareTrait;

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
     * Url generator instance.
     *
     * @var \Viserio\Component\Contract\Routing\UrlGenerator
     */
    protected $urlGenerator;

    /**
     * Assets renderer instance.
     *
     * @var \Viserio\Component\Contract\Profiler\AssetsRenderer
     */
    protected $assetsRenderer;

    /**
     * Enables or disables the profiler.
     *
     * @var bool
     */
    protected $enabled = false;

    /**
     * Template for Profiler.
     *
     * @var string
     */
    protected $template = __DIR__ . '/Resources/views/profiler.html.php';

    /**
     * Create new Profiler instance.
     *
     * @param \Viserio\Component\Contract\Profiler\AssetsRenderer $assetsRenderer
     */
    public function __construct(AssetsRendererContract $assetsRenderer)
    {
        $this->assetsRenderer = $assetsRenderer->setProfiler($this);
    }

    /**
     * Disables the profiler.
     *
     * @return void
     */
    public function disable(): void
    {
        $this->enabled = false;
    }

    /**
     * Enables the profiler.
     *
     * @return void
     */
    public function enable(): void
    {
        $this->enabled = true;
    }

    /**
     * {@inheritdoc}
     */
    public function setUrlGenerator(UrlGeneratorContract $urlGenerator): ProfilerContract
    {
        $this->urlGenerator = $urlGenerator;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrlGenerator(): ?UrlGeneratorContract
    {
        return $this->urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function setTemplate(string $path): ProfilerContract
    {
        $this->template = $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * {@inheritdoc}
     */
    public function addCollector(DataCollectorContract $collector, int $priority = 100): ProfilerContract
    {
        if (isset($this->collectors[$collector->getName()])) {
            throw new RuntimeException(\sprintf('[%s] is already a registered collector.', $collector->getName()));
        }

        $this->collectors[$collector->getName()] = [
            'collector' => $collector,
            'priority'  => $priority,
        ];

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasCollector(string $name): bool
    {
        return isset($this->collectors[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getCollectors(): array
    {
        return $this->collectors;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyResponse(
        ServerRequestInterface $serverRequest,
        ResponseInterface $response
    ): ResponseInterface {
        if ($this->runningInConsole() || ! $this->enabled) {
            return $response;
        }

        $token = \mb_substr(\hash('sha256', \uniqid((string) \mt_rand(), true)), 0, 6);
        $response->withHeader('X-Debug-Token', $token);
        //@TODO Send json data or redirect.
        try {
            if ($this->isRedirect($response)) {
                // $this->stackData();
            } elseif ($this->isJsonRequest($serverRequest)) {
                // $this->sendDataInHeaders(true);
            } elseif ($this->isHtmlResponse($response) || $this->isHtmlAccepted($serverRequest)) {
                // Just collect + store data, don't inject it.
                $this->collectData($token, $serverRequest, $response);
            }
        } catch (Throwable $exception) {
            if ($this->logger !== null) {
                $this->logger->error('Profiler exception: ' . $exception->getMessage());
            } else {
                throw $exception;
            }
        }

        return $this->injectProfiler($response, $token);
    }

    /**
     * Returns a AssetsRenderer for this instance.
     *
     * @return \Viserio\Component\Contract\Profiler\AssetsRenderer
     */
    public function getAssetsRenderer(): AssetsRendererContract
    {
        return $this->assetsRenderer;
    }

    /**
     * Injects the web debug toolbar into the given Response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param string                              $token
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @see https://github.com/symfony/ProfilerBundle/blob/master/EventListener/WebDebugToolbarListener.php
     */
    protected function injectProfiler(ResponseInterface $response, string $token): ResponseInterface
    {
        $content         = (string) $response->getBody();
        $renderedContent = $this->createTemplate($token);

        $pos = \mb_strripos($content, '</body>');

        if ($pos !== false) {
            $stream = $this->streamFactory->createStream(
                \mb_substr($content, 0, $pos) . $renderedContent . \mb_substr($content, $pos)
            );

            // Update the new content and reset the content length
            $response = $response->withoutHeader('Content-Length');

            return $response->withBody($stream);
        }

        $response->getBody()->write($renderedContent);

        return $response;
    }

    /**
     * Determine if we are running in the console.
     *
     * @return bool
     *
     * @codeCoverageIgnore
     */
    protected function runningInConsole(): bool
    {
        return PHP_SAPI == 'cli' || PHP_SAPI == 'phpdbg';
    }

    /**
     * Collect data and create a new profile and save it.
     *
     * @param string                                   $token
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return void
     */
    private function collectData(
        string $token,
        ServerRequestInterface $serverRequest,
        ResponseInterface $response
    ): void {
        // sort on priority
        \usort($this->collectors, function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        foreach ($this->collectors as $name => $collector) {
            $collector['collector']->collect($serverRequest, $response);

            $this->collectors[$name]['collector'] = $collector['collector'];
        }

        if ($this->cachePool !== null) {
            $this->createProfile(
                $token,
                (new ClientIp($serverRequest))->getIpAddress(),
                $serverRequest->getMethod(),
                (string) $serverRequest->getUri(),
                $response->getStatusCode(),
                \microtime(true),
                \date('Y-m-d H:i:s'),
                $this->collectors
            );
        }
    }

    /**
     * Create template.
     *
     * @param string $token
     *
     * @return string
     */
    private function createTemplate(string $token): string
    {
        $assets   = $this->getAssetsRenderer();
        $template = new TemplateManager(
            $this->collectors,
            $this->template,
            $token,
            $assets->getIcons()
        );

        return $assets->render() . $template->render();
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
        return $this->hasHeaderContains($response, 'Content-Type', 'html');
    }

    /**
     * Check if request is a json request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return bool
     */
    private function isJsonRequest(ServerRequestInterface $request): bool
    {
        return $this->hasHeaderContains($request, 'Accept', 'application/json');
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
        return $this->hasHeaderContains($request, 'Accept', 'html');
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
        return \mb_strpos($message->getHeaderLine($headerName), $value) !== false;
    }

    /**
     * Returns a boolean TRUE for if the response has redirect status code.
     *
     * Five common HTTP status codes indicates a redirection beginning from 301.
     * 304 not modified and 305 use proxy are not redirects.
     *
     * @see https://en.wikipedia.org/wiki/List_of_HTTP_status_codes#3xx_Redirection
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return bool
     */
    private function isRedirect(ResponseInterface $response): bool
    {
        return \in_array($response->getStatusCode(), [301, 302, 303, 307, 308], true);
    }

    /**
     * Create profile with all datas.
     *
     * @param string $token
     * @param string $ip
     * @param string $method
     * @param string $url
     * @param int    $statusCode
     * @param float  $time
     * @param string $date
     * @param array  $collectors
     *
     * @return void
     */
    private function createProfile(
        string $token,
        string $ip,
        string $method,
        string $url,
        int $statusCode,
        float $time,
        string $date,
        array $collectors
    ): void {
        $profile = new Profile($token);
        $profile->setIp($ip);
        $profile->setMethod($method);
        $profile->setUrl($url);
        $profile->setTime($time);
        $profile->setDate($date);
        $profile->setStatusCode($statusCode);
        $profile->setCollectors($collectors);

        $item = $this->cachePool->getItem($token);
        $item->set($profile);
        $item->expiresAfter(60);

        $this->cachePool->save($item);
    }
}
