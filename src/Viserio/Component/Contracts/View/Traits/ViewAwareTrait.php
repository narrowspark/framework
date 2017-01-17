<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\View\Traits;

use RuntimeException;
use Viserio\Component\Contracts\View\Factory as ViewFactoryContract;

trait ViewAwareTrait
{
    /**
     * View factory instance.
     *
     * @var \Viserio\Component\Contracts\View\Factory
     */
    protected $views;

    /**
     * Set a view factory instance.
     *
     * @param \Viserio\Component\Contracts\View\Factory $views
     *
     * @return $this
     */
    public function setViewFactory(ViewFactoryContract $views)
    {
        $this->views = $views;

        return $this;
    }

    /**
     * Get the view factory instance.
     *
     * @throws \RuntimeException
     *
     * @return \Viserio\Component\Contracts\View\Factory
     */
    public function getViewFactory(): ViewFactoryContract
    {
        if (! $this->views) {
            throw new RuntimeException('View factory is not set up.');
        }

        return $this->views;
    }
}
