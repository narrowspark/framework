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

namespace Viserio\Contract\Parser\Traits;

use Viserio\Contract\Parser\Loader as LoaderContract;

trait ParserAwareTrait
{
    /**
     * loader instance.
     *
     * @var null|\Viserio\Contract\Parser\Loader
     */
    protected $loader;

    /**
     * Set a loader instance.
     *
     * @return static
     */
    public function setLoader(LoaderContract $loader): self
    {
        $this->loader = $loader;

        return $this;
    }
}
