<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Parser\Traits;

use Viserio\Component\Contract\Parser\Exception\RuntimeException;
use Viserio\Component\Contract\Parser\Loader as LoaderContract;

trait ParserAwareTrait
{
    /**
     * loader instance.
     *
     * @var null|\Viserio\Component\Contract\Parser\Loader
     */
    protected $loader;

    /**
     * Get the loader instance.
     *
     * @throws \Viserio\Component\Contract\Parser\Exception\RuntimeException
     *
     * @return \Viserio\Component\Contract\Parser\Loader
     */
    public function getLoader(): LoaderContract
    {
        if (! $this->loader) {
            throw new RuntimeException('Loader is not set up.');
        }

        return $this->loader;
    }

    /**
     * Set a loader instance.
     *
     * @param \Viserio\Component\Contract\Parser\Loader $loader
     *
     * @return $this
     */
    public function setLoader(LoaderContract $loader)
    {
        $this->loader = $loader;

        return $this;
    }
}
