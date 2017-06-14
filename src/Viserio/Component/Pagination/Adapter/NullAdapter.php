<?php
declare(strict_types=1);
namespace Viserio\Component\Pagination\Adapter;

use Viserio\Component\Contracts\Pagination\Adapter as AdapterContract;

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
