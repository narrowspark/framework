<?php
declare(strict_types=1);
namespace Viserio\Component\Pagination\Presenter;

use Viserio\Component\Contracts\Pagination\Paginator as PaginatorContract;
use Viserio\Component\Contracts\Pagination\Presenter as PresenterContract;

class Bootstrap4 implements PresenterContract
{
    /**
     * Paginator instance.
     *
     * @var \Viserio\Component\Contracts\Pagination\Paginator
     */
    protected $paginator;

    /**
     * Create a new Bootstrap 4 presenter.
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
            $pagination = '<ul class="pagination">';

            // Previous Page Link
            if ($paginator->onFirstPage()) {
                $pagination .= '<li class="page-item disabled"><span class="page-link">&laquo;</span></li>';
            } else {
                $pagination .= '<li class="page-item"><a class="page-link" href="' . $paginator->getPreviousPageUrl() . '" rel="prev">&laquo;</a></li>';
            }

            if (\method_exists($paginator, 'getElements')) {
                $this->getPaginationsLinks($paginator->getElements(), $pagination);
            }

            // Next Page Link
            if ($paginator->hasMorePages()) {
                $pagination .= '<li class="page-item"><a class="page-link" href="' . $paginator->getNextPageUrl() . '" rel="next">&raquo;</a></li>';
            } else {
                $pagination .= '<li class="page-item disabled"><span class="page-link">&raquo;</span></li>';
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
    private function getPaginationsLinks(array $items, $pagination): void
    {
        foreach ($items as $item) {
            if (\is_string($item)) {
                $pagination .= '<li class="page-item disabled"><span class="page-link">' . $item . '</span></li>';
            }

            // Array Of Links
            if (\is_array($item)) {
                foreach ($item as $page => $url) {
                    if ($this->paginator->getCurrentPage() == $page) {
                        $pagination .= '<li class="page-item active"><span class="page-link">' . $page . '</span></li>';
                    } else {
                        $pagination .= '<li class="page-item"><a class="page-link" href="' . $url . '">' . $page . '</a></li>';
                    }
                }
            }
        }
    }
}
