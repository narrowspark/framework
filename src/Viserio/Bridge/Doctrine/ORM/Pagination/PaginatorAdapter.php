<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Pagination;

use Doctrine\ORM\AbstractQuery;

class PaginatorAdapter
{
    /**
     * A AbstractQuery instance.
     *
     * @var \Doctrine\ORM\AbstractQuery
     */
    protected $query;

    /**
     * @var int
     */
    private $perPage;

    /**
     * @var callable
     */
    private $pageResolver;

    /**
     * @var bool
     */
    private $fetchJoinCollection;

    /**
     * Create a new paginator adapter instance.
     *
     * @param \Doctrine\ORM\AbstractQuery $query
     * @param int                         $perPage
     * @param callable                    $pageResolver
     * @param bool                        $fetchJoinCollection
     */
    private function __construct(AbstractQuery $query, int $perPage, callable $pageResolver, bool $fetchJoinCollection)
    {
        $this->query               = $query;
        $this->perPage             = $perPage;
        $this->pageResolver        = $pageResolver;
        $this->fetchJoinCollection = $fetchJoinCollection;
    }
}
