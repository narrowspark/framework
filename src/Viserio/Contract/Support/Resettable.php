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

namespace Viserio\Contract\Support;

interface Resettable
{
    /**
     * Provides a way to reset an object to its initial state.
     *
     * When calling the "reset()" method on an object, it should be put back to its
     * initial state. This usually means clearing any internal buffers and forwarding
     * the call to internal dependencies. All properties of the object should be put
     * back to the same state it had when it was first ready to use.
     *
     * @return void
     */
    public function reset(): void;
}
