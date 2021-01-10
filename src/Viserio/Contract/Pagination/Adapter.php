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

namespace Viserio\Contract\Pagination;

interface Adapter
{
    /**
     * Returns an array of all items.
     */
    public function getItems(): array;

    /**
     * Returns an number of items for a page.
     */
    public function getItemsPerPage(): int;
}
