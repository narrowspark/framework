<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\WebProfiler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Component\Contracts\WebProfiler\DataCollector as DataCollectorContract;

interface WebProfiler
{
    /**
     * Disables the profiler.
     *
     * @return void
     */
    public function disable(): void;

    /**
     * Enables the profiler.
     *
     * @return void
     */
    public function enable(): void;

    /**
     * Set the webprofiler template path.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setTemplate(string $path): self;

    /**
     * Get the webprofiler template path.
     *
     * @return string
     */
    public function getTemplate(): string;

    /**
     * Adds a data collector.
     *
     * @param \Viserio\Component\Contracts\WebProfiler\DataCollector $collector
     * @param int                                                    $priority
     *
     * @throws \RuntimeException
     *
     * @return $this
     */
    public function addCollector(DataCollectorContract $collector, int $priority = 100): WebProfiler;

    /**
     * Checks if a data collector has been added.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasCollector(string $name): bool;

    /**
     * Returns an array of all data collectors.
     *
     * @return array
     */
    public function getCollectors(): array;

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
    ): ResponseInterface;

    /**
     * Set a url generator instance.
     *
     * @param \Viserio\Component\Contracts\Routing\UrlGenerator $urlGenerator
     *
     * @return $this
     */
    public function setUrlGenerator(UrlGeneratorContract $urlGenerator): self;

    /**
     * Get a url generator instance.
     *
     * @return \Viserio\Component\Contracts\Routing\UrlGenerator|null
     */
    public function getUrlGenerator(): ?UrlGeneratorContract;
}
