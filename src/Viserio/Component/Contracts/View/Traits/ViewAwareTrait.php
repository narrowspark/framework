<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\View\Traits;

use Viserio\Component\Contracts\View\Factory as ViewFactoryContract;

trait ViewAwareTrait
{
    /**
     * View factory instance.
     *
     * @var \Viserio\Component\Contracts\View\Factory
     */
    protected $viewFactory;

    /**
     * Set a view factory instance.
     *
     * @param \Viserio\Component\Contracts\View\Factory $viewFactory
     *
     * @return $this
     */
    public function setViewFactory(ViewFactoryContract $viewFactory)
    {
        $this->viewFactory = $viewFactory;

        return $this;
    }
}
