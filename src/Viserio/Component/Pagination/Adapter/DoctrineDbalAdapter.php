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

use Doctrine\DBAL\Query\QueryBuilder;
use InvalidArgumentException;
use Viserio\Contract\Pagination\Adapter as AdapterContract;

class DoctrineDbalAdapter implements AdapterContract
{
    /**
     * QueryBuilder instance.
     *
     * @var \Doctrine\DBAL\Query\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * Number of shown items per page.
     *
     * @var int
     */
    protected $itemsPerPage;

    /**
     * Create a new DoctrineDbal adapter.
     *
     * @throws InvalidArgumentException
     */
    public function __construct(QueryBuilder $queryBuilder, int $itemsPerPage)
    {
        if ($queryBuilder->getType() !== QueryBuilder::SELECT) {
            throw new InvalidArgumentException('Only SELECT queries can be paginated.');
        }

        $this->queryBuilder = $queryBuilder;
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
        return $this->queryBuilder->execute()->fetchAll();
    }
}
