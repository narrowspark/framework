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
     * @param \Doctrine\DBAL\Query\QueryBuilder $queryBuilder
     * @param int                               $itemsPerPage
     *
     * @throws \InvalidArgumentException
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
