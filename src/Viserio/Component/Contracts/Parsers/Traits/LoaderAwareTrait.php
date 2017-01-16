<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Parsers\Traits;

use RuntimeException;
use Viserio\Component\Contracts\Parsers\Loader as LoaderContract;

trait LoaderAwareTrait
{
    /**
     * loader instance.
     *
     * @var \Viserio\Component\Contracts\Parsers\Loader|null
     */
    protected $loader;

    /**
     * Set a loader instance.
     *
     * @param \Viserio\Component\Contracts\Parsers\Loader $loader
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
     * @return \Viserio\Component\Contracts\Parsers\Loader
     */
    public function getLoader(): LoaderContract
    {
        if (! $this->loader) {
            throw new RuntimeException('Loader is not set up.');
        }

        return $this->loader;
    }
}
