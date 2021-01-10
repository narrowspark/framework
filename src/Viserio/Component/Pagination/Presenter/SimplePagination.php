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

class SimplePagination implements PresenterContract
{
    /**
     * Paginator instance.
     *
     * @var \Viserio\Contract\Pagination\Paginator
     */
    protected $paginator;

    /**
     * Create a new Simple pagination presenter.
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
