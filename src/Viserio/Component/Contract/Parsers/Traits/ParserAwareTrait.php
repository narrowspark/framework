<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Parsers\Traits;

use RuntimeException;
use Viserio\Component\Contract\Parsers\Loader as LoaderContract;

trait ParserAwareTrait
{
    /**
     * loader instance.
     *
     * @var null|\Viserio\Component\Contract\Parsers\Loader
     */
    protected $loader;

    /**
     * Set a loader instance.
     *
     * @param \Viserio\Component\Contract\Parsers\Loader $loader
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
     * @return \Viserio\Component\Contract\Parsers\Loader
     */
    public function getLoader(): LoaderContract
    {
        if (! $this->loader) {
            throw new RuntimeException('Loader is not set up.');
        }

        return $this->loader;
    }
}
