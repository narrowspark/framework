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

interface DataCollector
{
    /**
     * Collects data for the given Request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return void
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void;

    /**
     * Returns the unique name of the collector.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns infos for a tab.
     *  - icon
     *  - label
     *  - value
     *  - class.
     *
     * @return array
     */
    public function getMenu(): array;

    /**
     * Get the Tab position from a collector.
     * Choose between left or right position.
     *
     * @return string
     */
    public function getMenuPosition(): string;

    /**
     * Resets this data collector to its initial state.
     *
     * @return void
     */
    public function reset(): void;
}
