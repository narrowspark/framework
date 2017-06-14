<?php
declare(strict_types=1);
namespace Viserio\Component\Pagination\Adapter;

use Viserio\Component\Contracts\Pagination\Adapter as AdapterContract;

class ArrayAdapter implements AdapterContract
{
    /**
     * Array of all items.
     *
     * @var array
     */
    protected $array;

    /**
     * Number of shown items per page.
     *
     * @var int
     */
    protected $itemsPerPage;

    /**
     * Create a new Array adapter.
     *
     * @param array $array
     * @param int   $itemsPerPage
     */
    public function __construct(array $array, int $itemsPerPage)
    {
        $this->array        = $array;
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(): array
    {
        return $this->array;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }
}
