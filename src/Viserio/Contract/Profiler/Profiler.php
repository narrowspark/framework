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

namespace Viserio\Contract\Profiler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contract\Profiler\DataCollector as DataCollectorContract;
use Viserio\Contract\Routing\UrlGenerator as UrlGeneratorContract;

interface Profiler
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
     * Set the Profiler template path.
     *
     * @param string $path
     *
     * @return self
     */
    public function setTemplate(string $path): self;

    /**
     * Get the Profiler template path.
     *
     * @return string
     */
    public function getTemplate(): string;

    /**
     * Returns a AssetsRenderer for this instance.
     *
     * @return \Viserio\Contract\Profiler\AssetsRenderer
     */
    public function getAssetsRenderer(): AssetsRenderer;

    /**
     * Adds a data collector.
     *
     * @param \Viserio\Contract\Profiler\DataCollector $collector
     * @param int                                      $priority
     *
     * @throws \RuntimeException
     *
     * @return self
     */
    public function addCollector(DataCollectorContract $collector, int $priority = 100): self;

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
     * @param \Viserio\Contract\Routing\UrlGenerator $urlGenerator
     *
     * @return self
     */
    public function setUrlGenerator(UrlGeneratorContract $urlGenerator): self;

    /**
     * Get a url generator instance.
     *
     * @return null|\Viserio\Contract\Routing\UrlGenerator
     */
    public function getUrlGenerator(): ?UrlGeneratorContract;

    /**
     * Reset the profiler data.
     *
     * @return void
     */
    public function reset(): void;
}
