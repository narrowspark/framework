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

use Viserio\Contract\Support\Renderable as RenderableContract;

interface AssetsRenderer extends RenderableContract
{
    /**
     * Set the Profiler.
     *
     * @param \Viserio\Contract\Profiler\Profiler $profiler
     *
     * @return self
     */
    public function setProfiler(Profiler $profiler): self;

    /**
     * Add icon to list.
     *
     * @param string $name
     * @param string $path
     *
     * @return self
     */
    public function setIcon(string $name, string $path): self;

    /**
     * Get all registered icons.
     *
     * @return array
     */
    public function getIcons(): array;

    /**
     * Ignores widgets provided by a collector.
     *
     * @param string $name
     *
     * @return self
     */
    public function setIgnoredCollector(string $name): self;

    /**
     * Returns the list of ignored collectors.
     *
     * @return array
     */
    public function getIgnoredCollectors(): array;

    /**
     * Return assets as a string.
     *
     * @param string $type 'js' or 'css'
     *
     * @return string
     */
    public function dumpAssetsToString(string $type): string;

    /**
     * Returns the list of asset files.
     *
     * @param null|string $type Only return css or js files
     *
     * @return array
     */
    public function getAssets(?string $type = null): array;
}
