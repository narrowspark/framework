<?php
declare(strict_types=1);
namespace Viserio\Component\Pagination\Presenters;

use Viserio\Component\Contracts\Pagination\Paginator as PaginatorContract;
use Viserio\Component\Contracts\Pagination\Presenter as PresenterContract;

class SemanticUi implements PresenterContract
{
    /**
     * Paginator instance.
     *
     * @var \Viserio\Component\Contracts\Pagination\Paginator
     */
    protected $paginator;

    /**
     * Create a new semantic-ui presenter.
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
            $pagination = '<div class="ui pagination menu">';

            // Previous Page Link
            if ($paginator->onFirstPage()) {
                $pagination .= '<a class="icon item disabled"><i class="left chevron icon"></i></a>';
            } else {
                $pagination .= '<a class="icon item" href="' . $paginator->getPreviousPageUrl() . '" rel="prev"><i class="left chevron icon"></i></a>';
            }

            if (method_exists($paginator, 'getElements')) {
                $this->getPaginationsLinks($paginator->getElements(), $pagination);
            }

            // Next Page Link
            if ($paginator->hasMorePages()) {
                $pagination .= '<a class="icon item" href="' . $paginator->getNextPageUrl() . '" rel="next"><i class="right chevron icon"></i></a>';
            } else {
                $pagination .= '<a class="icon item disabled"><i class="right chevron icon"></i></a>';
            }

            $pagination .= '</div>';

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
            if (is_string($item)) {
                $pagination .= '<a class="icon item disabled">' . $item . '</a>';
            }

            // Array Of Links
            if (is_array($item)) {
                foreach ($item as $page => $url) {
                    if ($this->paginator->getCurrentPage() == $page) {
                        $pagination .= '<a class="item active">' . $page . '</a>';
                    } else {
                        $pagination .= '<a class="item" href="' . $url . '">' . $page . '</a>';
                    }
                }
            }
        }
    }
}
