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

namespace Viserio\Component\Pagination\Adapter;

use Viserio\Contract\Pagination\Adapter as AdapterContract;

class NullAdapter implements AdapterContract
{
    /**
     * {@inheritdoc}
     */
    public function getItems(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsPerPage(): int
    {
        return 0;
    }
}
