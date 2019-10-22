<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Contract\View\Traits;

use Viserio\Contract\View\Factory as ViewFactoryContract;

trait ViewAwareTrait
{
    /**
     * View factory instance.
     *
     * @var null|\Viserio\Contract\View\Factory
     */
    protected $viewFactory;

    /**
     * Set a view factory instance.
     *
     * @param \Viserio\Contract\View\Factory $viewFactory
     *
     * @return static
     */
    public function setViewFactory(ViewFactoryContract $viewFactory): self
    {
        $this->viewFactory = $viewFactory;

        return $this;
    }
}
