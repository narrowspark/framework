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

namespace Viserio\Contract\Queue;

interface Factory
{
    /**
     * Get a connection instance.
     *
     * @param null|string $name
     *
     * @return mixed
     */
    public function getConnection(string $name = null);
}
