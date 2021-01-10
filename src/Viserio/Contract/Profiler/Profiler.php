<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\Profiler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Viserio\Contract\Profiler\DataCollector as DataCollectorContract;
use Viserio\Contract\Routing\UrlGenerator as UrlGeneratorContract;

interface Profiler
{
    /**
     * Disables the profiler.
     */
    public function disable(): void;

    /**
     * Enables the profiler.
     */
    public function enable(): void;

    /**
     * Set the Profiler template path.
     */
    public function setTemplate(string $path): self;

    /**
     * Get the Profiler template path.
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
     * @throws RuntimeException
     */
    public function addCollector(DataCollectorContract $collector, int $priority = 100): self;

    /**
     * Checks if a data collector has been added.
     */
    public function hasCollector(string $name): bool;

    /**
     * Returns an array of all data collectors.
     */
    public function getCollectors(): array;

    /**
     * Modify the response and inject the debugbar.
     */
    public function modifyResponse(
        ServerRequestInterface $serverRequest,
        ResponseInterface $response
    ): ResponseInterface;

    /**
     * Set a url generator instance.
     */
    public function setUrlGenerator(UrlGeneratorContract $urlGenerator): self;

    /**
     * Get a url generator instance.
     */
    public function getUrlGenerator(): ?UrlGeneratorContract;

    /**
     * Reset the profiler data.
     */
    public function reset(): void;
}
