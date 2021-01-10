<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
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
     * @return static
     */
    public function setViewFactory(ViewFactoryContract $viewFactory): self
    {
        $this->viewFactory = $viewFactory;

        return $this;
    }
}
