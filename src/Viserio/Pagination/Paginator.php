<?php
declare(strict_types=1);
namespace Viserio\Pagination;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Narrowspark\Collection\Collection;
use Viserio\Contracts\Pagination\Paginator as PaginatorContract;
use Viserio\Contracts\Pagination\Presenter as PresenterContract;
use Viserio\Contracts\View\Traits\ViewAwareTrait;

class Paginator extends AbstractPaginator implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable, PaginatorContract
{
    use ViewAwareTrait;

    /**
     * Render the paginator using the given view.
     *
     * @param \Viserio\Contracts\Pagination\Presenter|string|null $view
     *
     * @return string
     */
    public function render($view = null)
    {
        if (is_string($view) && $this->view !== null) {
            return $this->getViewFactory()->render($view, $this);
        } elseif ($view instanceof PresenterContract) {
            return $view($this)->render();
        }

        return 'default';
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }
}
