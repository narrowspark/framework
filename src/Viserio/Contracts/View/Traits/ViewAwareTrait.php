<?php
declare(strict_types=1);
namespace Viserio\Contracts\View\Traits;

use RuntimeException;
use Viserio\Contracts\View\Factory as ViewFactoryContract;

trait ViewAwareTrait
{
    /**
     * View factory instance.
     *
     * @var \Viserio\Contracts\View\Factory
     */
    protected $views;

    /**
     * Set a view factory instance.
     *
     * @param \Viserio\Contracts\View\Factory $views
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
     * @return \Viserio\Contracts\View\Factory
     */
    public function getViewFactory(): ViewFactoryContract
    {
        if (! $this->views) {
            throw new RuntimeException('View factory is not set up.');
        }

        return $this->views;
    }
}
