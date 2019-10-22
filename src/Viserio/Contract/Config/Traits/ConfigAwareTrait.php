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

namespace Viserio\Contract\Config\Traits;

use Viserio\Contract\Config\Repository as RepositoryContract;

trait ConfigAwareTrait
{
    /**
     * Config instance.
     *
     * @var null|\Viserio\Contract\Config\Repository
     */
    protected $config;

    /**
     * Set a Config.
     *
     * @param \Viserio\Contract\Config\Repository $config
     *
     * @return static
     */
    public function setConfig(RepositoryContract $config): self
    {
        $this->config = $config;

        return $this;
    }
}
