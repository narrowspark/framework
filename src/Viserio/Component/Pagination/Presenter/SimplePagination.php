<?php
declare(strict_types=1);
namespace Viserio\Component\Pagination\Presenter;

use Viserio\Component\Contract\Pagination\Paginator as PaginatorContract;
use Viserio\Component\Contract\Pagination\Presenter as PresenterContract;

class SimplePagination implements PresenterContract
{
    /**
     * Paginator instance.
     *
     * @var \Viserio\Component\Contract\Pagination\Paginator
     */
    protected $paginator;

    /**
     * Create a new Simple pagination presenter.
     *
     * @param \Viserio\Component\Contract\Pagination\Paginator $paginator
     */
    public function __construct(PaginatorContract $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * {@inheritdoc}
     */
    public function render(): string
    {
        $paginator = $this->paginator;

        if ($paginator->hasPages()) {
            $pagination = '<ul class="pagination">';

            // Previous Page Link
            if ($paginator->onFirstPage()) {
                $pagination .= '<li>&laquo;</li>';
            } else {
                $pagination .= '<li><a href="' . $paginator->getPreviousPageUrl() . '" rel="prev">&laquo;</a></li>';
            }

            // Next Page Link
            if ($paginator->hasMorePages()) {
                $pagination .= '<li><a href="' . $paginator->getNextPageUrl() . '" rel="next">&raquo;</a></li>';
            } else {
                $pagination .= '<li>&raquo;</li>';
            }

            $pagination .= '</ul>';

            return $pagination;
        }

        return '';
    }
}
