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

namespace Viserio\Component\Support\Traits;

use Viserio\Component\Support\Invoker;

trait InvokerAwareTrait
{
    /**
     * The invoker instance.
     *
     * @var \Viserio\Component\Support\Invoker
     */
    protected $invoker;

    /**
     * Get configured invoker.
     *
     * @return \Viserio\Component\Support\Invoker
     */
    protected function getInvoker(): Invoker
    {
        if ($this->invoker === null) {
            $this->invoker = new Invoker();

            if ($this->container !== null) {
                $this->invoker->setContainer($this->container)
                    ->injectByTypeHint(true)
                    ->injectByParameterName(true);
            }
        }

        return $this->invoker;
    }
}
