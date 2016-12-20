<?php
declare(strict_types=1);
namespace Viserio\Contracts\Parsers\Traits;

use RuntimeException;
use Viserio\Contracts\Parsers\Loader as LoaderContract;

trait LoaderAwareTrait
{
    /**
     * loader instance.
     *
     * @var \Viserio\Contracts\Parsers\Loader|null
     */
    protected $loader;

    /**
     * Set a loader instance.
     *
     * @param \Viserio\Contracts\Parsers\Loader $loader
     *
     * @return $this
     */
    public function setLoader(LoaderContract $loader)
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * Get the loader instance.
     *
     * @throws \RuntimeException
     *
     * @return \Viserio\Contracts\Parsers\Loader
     */
    public function getLoader(): LoaderContract
    {
        if (!$this->loader) {
            throw new RuntimeException('Loader is not set up.');
        }

        return $this->loader;
    }
}
