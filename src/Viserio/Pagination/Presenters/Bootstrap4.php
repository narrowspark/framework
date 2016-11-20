<?php
declare(strict_types=1);
namespace Viserio\Pagination\Presenters;

use Viserio\Contracts\Pagination\Paginator as PaginatorContract;
use Viserio\Contracts\Pagination\Presenter as PresenterContract;

class Bootstrap4 implements PresenterContract
{
    /**
     * Paginator instance.
     *
     * @var \Viserio\Contracts\Pagination\Paginator
     */
    protected $paginator;

    /**
     * Create a new Bootstrap 4 presenter.
     *
     * @param \Viserio\Contracts\Pagination\Paginator $paginator
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
    }
}
