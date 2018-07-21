<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Profiler;

use Viserio\Component\Contract\Support\Renderable as RenderableContract;

interface AssetsRenderer extends RenderableContract
{
    /**
     * Set the Profiler.
     *
     * @param \Viserio\Component\Contract\Profiler\Profiler $profiler
     *
     * @return \Viserio\Component\Contract\Profiler\AssetsRenderer
     */
    public function setProfiler(Profiler $profiler): self;

    /**
     * Add icon to list.
     *
     * @param string $name
     * @param string $path
     *
     * @return \Viserio\Component\Contract\Profiler\AssetsRenderer
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
     * @return \Viserio\Component\Contract\Profiler\AssetsRenderer
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
