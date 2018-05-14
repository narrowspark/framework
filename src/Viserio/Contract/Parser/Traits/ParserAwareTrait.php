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
     * @param \Viserio\Contract\Parser\Loader $loader
     *
     * @return static
     */
    public function setLoader(LoaderContract $loader): self
    {
        $this->loader = $loader;

        return $this;
    }
}
