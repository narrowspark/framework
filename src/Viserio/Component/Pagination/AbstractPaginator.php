<?php
declare(strict_types=1);
namespace Viserio\Component\Pagination;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Narrowspark\Collection\Collection;
use Viserio\Component\Contracts\Pagination\Paginator as PaginatorContract;
use Viserio\Component\Contracts\Support\Arrayable as ArrayableContract;
use Viserio\Component\Contracts\Support\Jsonable as JsonableContract;
use Viserio\Component\Contracts\Support\Stringable as StringableContract;

abstract class AbstractPaginator implements
    ArrayAccess,
    Countable,
    IteratorAggregate,
    StringableContract,
    ArrayableContract,
    JsonSerializable,
    JsonableContract,
    PaginatorContract
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
    protected $itemCountPerPage;

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
    protected $currentPageResolver;

    /**
     * The current request instance.
     *
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * Make dynamic calls into the collection.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     *
     * @codeCoverageIgnore
     */
    public function __call($method, $parameters)
    {
        return $this->items->$method(...$parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return (string) $this->render();
    }

    /**
     * {@inheritdoc}
     */
    public function getUrlRange(int $start, int $end): string
    {
        $urls = [];

        for ($page = $start; $page <= $end; ++$page) {
            $urls[$page] = $this->getUrl($page);
        }

        return $urls;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrl(int $page): string
    {
        if ($page <= 0) {
            $page = 1;
        }

        // Extra information like sortings storage.
        $parameters = [$this->pageName => $page];

        if (count($this->query) > 0) {
            $parameters = array_merge($this->query, $parameters);
        }

        $url = $this->path;
        $url .= (mb_strpos($this->path, '?') !== false ? '&' : '?');
        $url .= http_build_query($parameters, '', '&');
        $url .= $this->buildFragment();

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function getPreviousPageUrl()
    {
        if ($this->getCurrentPage() > 1) {
            return $this->getUrl($this->getCurrentPage() - 1);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addQuery(string $key, string $value)
    {
        if ($this->pageName !== $key) {
            $this->query[$key] = $value;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function setFragment(string $fragment)
    {
        $this->fragment = $fragment;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * {@inheritdoc}
     */
    public function appends($key, string $value = null)
    {
        if (is_array($key)) {
            return $this->appendArray($key);
        }

        return $this->addQuery($key, $value);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getPageName(): string
    {
        return $this->pageName;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function setPageName(string $name)
    {
        $this->pageName = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPath(string $path)
    {
        $this->path = $path != '/' ? rtrim($path, '/') : $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     *
     * @codeCoverageIgnore
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items->all());
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
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
     * {@inheritdoc}
     */
    public function getItems(): array
    {
        return $this->items->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstItem(): int
    {
        if (count($this->items) === 0) {
            return 0;
        }

        return ($this->currentPage - 1) * $this->itemCountPerPage + 1;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastItem(): int
    {
        if (count($this->items) === 0) {
            return 0;
        }

        return $this->getFirstItem() + $this->count() - 1;
    }

    /**
     * {@inheritdoc}
     */
    public function onFirstPage(): bool
    {
        return $this->getCurrentPage() <= 1;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getItemsPerPage(): int
    {
        return $this->itemCountPerPage;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * {@inheritdoc}
     */
    public function hasPages(): bool
    {
        return ! ($this->getCurrentPage() == 1 && ! $this->hasMorePages());
    }

    /**
     * Set the paginator's underlying collection.
     *
     * @param \Narrowspark\Collection\Collection $collection
     *
     * @return $this
     *
     * @codeCoverageIgnore
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
     *
     * @codeCoverageIgnore
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
     *
     * @codeCoverageIgnore
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
     *
     * @codeCoverageIgnore
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
     *
     * @codeCoverageIgnore
     */
    public function offsetSet($key, $value)
    {
        $this->items->put($key, $value);
    }

    /**
     * Unset the item at the given key.
     *
     * @param mixed $key
     *
     * @codeCoverageIgnore
     */
    public function offsetUnset($key)
    {
        $this->items->forget($key);
    }

    /**
     * Build the full fragment portion of a URL.
     *
     * @return string
     */
    protected function buildFragment(): string
    {
        return $this->fragment ? '#' . $this->fragment : '';
    }

    /**
     * Add an array of query string values.
     *
     * @param array $keys
     *
     * @return $this
     */
    protected function appendArray(array $keys)
    {
        foreach ($keys as $key => $value) {
            $this->addQuery($key, $value);
        }

        return $this;
    }

    /**
     * Determine if the given value is a valid page number.
     *
     * @param int $page
     *
     * @return bool
     */
    protected function isValidPageNumber(int $page): bool
    {
        return $page >= 1 && filter_var($page, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Resolve the current page or return the default value.
     *
     * @return int
     */
    protected function resolveCurrentPage(): int
    {
        $query = $this->request->getQueryParams();

        if (array_key_exists($this->pageName, $query)) {
            $query = $this->secureInput($query);
            $page  = $query[$this->pageName];

            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }
        }

        return 1;
    }

    /**
     * At least check if the input string does not have null-byte
     * and is a UTF-8 valid string.
     *
     * @param array $query
     *
     * @return array
     */
    private function secureInput(array $query): array
    {
        $secure = function (&$v) {
            if (! is_string($v) && ! is_numeric($v)) {
                $v = '';
            } elseif (mb_strpos($v, "\0") !== false) {
                $v = '';
            } elseif (! mb_check_encoding($v, 'UTF-8')) {
                $v = '';
            }
        };

        array_walk_recursive($query, $secure);

        return $query;
    }
}
