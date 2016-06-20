<?php
namespace Viserio\Support\Traits;

use Viserio\Contracts\Parsers\Loader as LoaderContract;

trait FileLoaderAwareTrait
{
    /**
     * Fileloader instance.
     *
     * @var \Viserio\Contracts\Parsers\Loader
     */
    protected $loader;

    /**
     * Set the file loader.
     *
     * @param \Viserio\Contracts\Parsers\Loader $loader
     *
     * @return self
     */
    public function setLoader(LoaderContract $loader)
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * Get the file loader.
     *
     * @return \Viserio\Contracts\Parsers\Loader
     */
    public function getLoader()
    {
        return $this->loader;
    }
}
