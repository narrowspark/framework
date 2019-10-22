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

namespace Viserio\Component\Profiler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use RuntimeException;
use Throwable;
use Viserio\Component\Support\Http\ClientIp;
use Viserio\Contract\Cache\Traits\CacheItemPoolAwareTrait;
use Viserio\Contract\Events\Traits\EventManagerAwareTrait;
use Viserio\Contract\HttpFactory\Traits\StreamFactoryAwareTrait;
use Viserio\Contract\Profiler\AssetsRenderer as AssetsRendererContract;
use Viserio\Contract\Profiler\DataCollector as DataCollectorContract;
use Viserio\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Contract\Routing\UrlGenerator as UrlGeneratorContract;

class Profiler implements LoggerAwareInterface, ProfilerContract
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
     * @var \Viserio\Contract\Routing\UrlGenerator
     */
    protected $urlGenerator;

    /**
     * Assets renderer instance.
     *
     * @var \Viserio\Contract\Profiler\AssetsRenderer
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
    protected $template = __DIR__ . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'views' . \DIRECTORY_SEPARATOR . 'profiler.html.php';

    /**
     * Create new Profiler instance.
     *
     * @param \Viserio\Contract\Profiler\AssetsRenderer $assetsRenderer
     */
    public function __construct(AssetsRendererContract $assetsRenderer)
    {
        $this->assetsRenderer = $assetsRenderer->setProfiler($this);
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
    public function getUrlGenerator(): ?UrlGeneratorContract
    {
        return $this->urlGenerator;
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
     * Returns a AssetsRenderer for this instance.
     *
     * @return \Viserio\Contract\Profiler\AssetsRenderer
     */
    public function getAssetsRenderer(): AssetsRendererContract
    {
        return $this->assetsRenderer;
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
    public function setTemplate(string $path): ProfilerContract
    {
        $this->template = $path;

        return $this;
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
    public function addCollector(DataCollectorContract $collector, int $priority = 100): ProfilerContract
    {
        if (isset($this->collectors[$collector->getName()])) {
            throw new RuntimeException(\sprintf('[%s] is already a registered collector.', $collector->getName()));
        }

        $this->collectors[$collector->getName()] = [
            'collector' => $collector,
            'priority' => $priority,
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
    public function modifyResponse(
        ServerRequestInterface $serverRequest,
        ResponseInterface $response
    ): ResponseInterface {
        if ($this->runningInConsole() || ! $this->enabled) {
            return $response;
        }

        $token = \substr(\hash('sha256', \uniqid((string) \mt_rand(), true)), 0, 6);
        $response->withHeader('x-debug-token', $token);

        try {
            $this->collectData($token, $serverRequest, $response);
        } catch (Throwable $exception) {
            if ($this->logger !== null) {
                $this->logger->error('Profiler exception: ' . $exception->getMessage());
            } else {
                throw $exception;
            }
        }

        if ($this->isHtmlResponse($response)) {
            $response = $this->injectProfiler($response, $token);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        foreach ($this->collectors as $data) {
            if (isset($data['collector'])) {
                $collector = $data['collector'];

                $collector->reset();
            }
        }
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
        $content = (string) $response->getBody();
        $renderedContent = $this->createTemplate($token);

        $pos = \strripos($content, '</body>');

        if ($pos !== false) {
            $stream = $this->streamFactory->createStream(
                \substr($content, 0, $pos) . $renderedContent . \substr($content, $pos)
            );

            // Update the new content and reset the content length
            $response = $response->withoutHeader('content-length');

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
        return \in_array(\PHP_SAPI, ['cli', 'phpdbg'], true);
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
        \usort($this->collectors, static function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        foreach ($this->collectors as $name => $collector) {
            $collector['collector']->collect($serverRequest, $response);

            $this->collectors[$name]['collector'] = $collector['collector'];
        }

        $ip = (new ClientIp($serverRequest))->getIpAddress();

        if ($this->cacheItemPool !== null) {
            $this->createProfile(
                $token,
                $ip ?? 'Unknown',
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
        $assets = $this->getAssetsRenderer();
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
        return \strpos($response->getHeaderLine('Content-Type'), 'html', 0) !== false;
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

        $item = $this->cacheItemPool->getItem($token);
        $item->set($profile);
        $item->expiresAfter(60);

        $this->cacheItemPool->save($item);
    }
}
