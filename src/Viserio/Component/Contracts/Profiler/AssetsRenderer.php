<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Profiler;

use Viserio\Component\Contracts\Support\Renderable as RenderableContract;

interface AssetsRenderer extends RenderableContract
{
    /**
     * Set the Profiler.
     *
     * @param \Viserio\Component\Contracts\Profiler\Profiler $profiler
     *
     * @return $this
     */
    public function setProfiler(Profiler $profiler): self;

    /**
     * Add icon to list.
     *
     * @param string $name
     * @param string $path
     *
     * @return $this
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
     * @return $this
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
     * @param string|null $type Only return css or js files
     *
     * @return array
     */
    public function getAssets(?string $type = null): array;
}
