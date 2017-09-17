<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Pagination;

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
