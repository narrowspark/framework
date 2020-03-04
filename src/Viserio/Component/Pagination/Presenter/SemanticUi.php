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

namespace Viserio\Component\Pagination\Presenter;

use Viserio\Contract\Pagination\Paginator as PaginatorContract;
use Viserio\Contract\Pagination\Presenter as PresenterContract;

class SemanticUi implements PresenterContract
{
    /**
     * Paginator instance.
     *
     * @var \Viserio\Contract\Pagination\Paginator
     */
    protected $paginator;

    /**
     * Create a new semantic-ui presenter.
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

            if (\method_exists($paginator, 'getElements')) {
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
     * @param string $pagination
     */
    private function getPaginationsLinks(array $items, $pagination): void
    {
        foreach ($items as $item) {
            if (\is_string($item)) {
                $pagination .= '<a class="icon item disabled">' . $item . '</a>';
            }

            // Array Of Links
            if (\is_array($item)) {
                foreach ($item as $page => $url) {
                    if ($this->paginator->getCurrentPage() === $page) {
                        $pagination .= '<a class="item active">' . $page . '</a>';
                    } else {
                        $pagination .= '<a class="item" href="' . $url . '">' . $page . '</a>';
                    }
                }
            }
        }
    }
}
