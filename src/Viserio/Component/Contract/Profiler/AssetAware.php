<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Profiler;

interface AssetAware extends DataCollector
{
    /**
     * Returns an array with the following keys:
     *  - css: an array of filenames
     *  - js: an array of filenames.
     *
     * @return array
     */
    public function getAssets(): array;
}
