<?php
declare(strict_types=1);
namespace Viserio\Pagination;

use ArrayIterator;
use Narrowspark\Collection\Collection;

abstract class AbstractPaginator
{
    /**
     * All of the items being paginated.
     *
     * @var \Narrowspark\Collection\Collection
     */
    protected $items;

    /**
     * The number of items to be shown per page.
     *
     * @var int
     */
    protected $perPage;

    /**
     * The current page being "viewed".
     *
     * @var int
     */
    protected $currentPage;

    /**
     * The base path to assign to all URLs.
     *
     * @var string
     */
    protected $path = '/';

    /**
     * The query parameters to add to all URLs.
     *
     * @var array
     */
    protected $query = [];

    /**
     * The URL fragment to add to all URLs.
     *
     * @var string|null
     */
    protected $fragment = null;

    /**
     * The query string variable used to store the page.
     *
     * @var string
     */
    protected $pageName = 'page';

    /**
     * The current page resolver callback.
     *
     * @var \Closure
     */
    protected $currentPathResolver;

    /**
     * The current page resolver callback.
     *
     * @var \Closure
     */
    protected $currentPageResolver;

    /**
     * Set the base path to assign to all URLs.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setPath(string $path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items->all());
    }

    /**
     * Determine if the list of items is empty or not.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * Get the number of items for the current page.
     *
     * @return int
     */
    public function count()
    {
        return $this->items->count();
    }

    /**
     * Set the paginator's underlying collection.
     *
     * @param \Narrowspark\Collection\Collection $collection
     *
     * @return $this
     */
    public function setCollection(Collection $collection)
    {
        $this->items = $collection;

        return $this;
    }

    /**
     * Get the paginator's underlying collection.
     *
     * @return \Narrowspark\Collection\Collection
     */
    public function getCollection(): Collection
    {
        return $this->items;
    }

    /**
     * Determine if the given item exists.
     *
     * @param mixed $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->items->has($key);
    }

    /**
     * Get the item at the given offset.
     *
     * @param mixed $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->items->get($key);
    }

    /**
     * Set the item at the given offset.
     *
     * @param mixed $key
     * @param mixed $value
     */
    public function offsetSet($key, $value)
    {
        $this->items->put($key, $value);
    }

    /**
     * Unset the item at the given key.
     *
     * @param mixed $key
     */
    public function offsetUnset($key)
    {
        $this->items->forget($key);
    }

    /**
     * Make dynamic calls into the collection.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->items->$method(...$parameters);
    }

    /**
     * Render the contents of the paginator when casting to string.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->render();
    }
}
