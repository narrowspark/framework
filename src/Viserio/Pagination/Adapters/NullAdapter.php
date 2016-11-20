<?php
declare(strict_types=1);
namespace Viserio\Pagination\Adapters;

use Viserio\Contracts\Pagination\Adapter as AdapterContract;

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
