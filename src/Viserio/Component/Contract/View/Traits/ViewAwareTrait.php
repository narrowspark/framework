<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\View\Traits;

use Viserio\Component\Contract\View\Factory as ViewFactoryContract;

trait ViewAwareTrait
{
    /**
     * View factory instance.
     *
     * @var \Viserio\Component\Contract\View\Factory
     */
    protected $viewFactory;

    /**
     * Set a view factory instance.
     *
     * @param \Viserio\Component\Contract\View\Factory $viewFactory
     *
     * @return $this
     */
    public function setViewFactory(ViewFactoryContract $viewFactory)
    {
        $this->viewFactory = $viewFactory;

        return $this;
    }
}
