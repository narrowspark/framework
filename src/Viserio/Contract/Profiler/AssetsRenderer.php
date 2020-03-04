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

use Viserio\Contract\Support\Renderable as RenderableContract;

interface AssetsRenderer extends RenderableContract
{
    /**
     * Set the Profiler.
     *
     * @param \Viserio\Contract\Profiler\Profiler $profiler
     */
    public function setProfiler(Profiler $profiler): self;

    /**
     * Add icon to list.
     */
    public function setIcon(string $name, string $path): self;

    /**
     * Get all registered icons.
     */
    public function getIcons(): array;

    /**
     * Ignores widgets provided by a collector.
     */
    public function setIgnoredCollector(string $name): self;

    /**
     * Returns the list of ignored collectors.
     */
    public function getIgnoredCollectors(): array;

    /**
     * Return assets as a string.
     *
     * @param string $type 'js' or 'css'
     */
    public function dumpAssetsToString(string $type): string;

    /**
     * Returns the list of asset files.
     *
     * @param null|string $type Only return css or js files
     */
    public function getAssets(?string $type = null): array;
}
