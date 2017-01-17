<?php
declare(strict_types=1);
namespace Viserio\Component\Pagination\Presenters;

use Viserio\Component\Contracts\Pagination\Paginator as PaginatorContract;
use Viserio\Component\Contracts\Pagination\Presenter as PresenterContract;

class Foundation6 implements PresenterContract
{
    /**
     * Paginator instance.
     *
     * @var \Viserio\Component\Contracts\Pagination\Paginator
     */
    protected $paginator;

    /**
     * Create a new Foundation 6 presenter.
     *
     * @param \Viserio\Component\Contracts\Pagination\Paginator $paginator
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
            $pagination = '<ul class="pagination" role="navigation">';

            // Previous Page Link
            if ($paginator->onFirstPage()) {
                $pagination .= '<li class="pagination-previous disabled">&laquo;</li>';
            } else {
                $pagination .= '<li class="pagination-previous"><a href="' . $paginator->getPreviousPageUrl() . '" rel="prev">&laquo;</a></li>';
            }

            if (method_exists($paginator, 'getElements')) {
                $this->getPaginationsLinks($paginator->getElements(), $pagination);
            }

            // Next Page Link
            if ($paginator->hasMorePages()) {
                $pagination .= '<li class="pagination-next"><a href="' . $paginator->getNextPageUrl() . '" rel="next">&raquo;</a></li>';
            } else {
                $pagination .= '<li class="pagination-next disabled">&raquo;</li>';
            }

            $pagination .= '</ul>';

            return $pagination;
        }

        return '';
    }

    /**
     * Get all paginations page links.
     *
     * @param array  $items
     * @param string $pagination
     */
    private function getPaginationsLinks(array $items, $pagination)
    {
        foreach ($items as $item) {
            // "Three Dots" Separator
            if (is_string($item)) {
                $pagination .= '<li class="ellipsis"></li>';
            }

            // Array Of Links
            if (is_array($item)) {
                foreach ($item as $page => $url) {
                    if ($this->paginator->getCurrentPage() == $page) {
                        $pagination .= '<li class="current">' . $page . '</li>';
                    } else {
                        $pagination .= '<li><a href="' . $url . '">' . $page . '</a></li>';
                    }
                }
            }
        }
    }
}
