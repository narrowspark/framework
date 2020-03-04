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

interface DataCollector
{
    /**
     * Collects data for the given Request.
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void;

    /**
     * Returns the unique name of the collector.
     */
    public function getName(): string;

    /**
     * Returns infos for a tab.
     *  - icon
     *  - label
     *  - value
     *  - class.
     */
    public function getMenu(): array;

    /**
     * Get the Tab position from a collector.
     * Choose between left or right position.
     */
    public function getMenuPosition(): string;

    /**
     * Resets this data collector to its initial state.
     */
    public function reset(): void;
}
