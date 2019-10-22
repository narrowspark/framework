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
