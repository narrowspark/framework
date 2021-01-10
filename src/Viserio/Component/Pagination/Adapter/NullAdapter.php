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
