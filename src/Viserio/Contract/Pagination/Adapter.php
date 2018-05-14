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

namespace Viserio\Contract\Pagination;

interface Adapter
{
    /**
     * Returns an array of all items.
     *
     * @return array
     */
    public function getItems(): array;

    /**
     * Returns an number of items for a page.
     *
     * @return int
     */
    public function getItemsPerPage(): int;
}
