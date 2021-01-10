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
     */
    public function __construct(array $array, int $itemsPerPage)
    {
        $this->array = $array;
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsPerPage(): int
    {
        return $this->itemsPerPage;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(): array
    {
        return $this->array;
    }
}
