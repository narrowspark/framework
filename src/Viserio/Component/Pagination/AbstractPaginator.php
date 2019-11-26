<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Pagination;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Narrowspark\Collection\Collection;
use Throwable;
use Viserio\Contract\Pagination\Paginator as PaginatorContract;
use Viserio\Contract\Support\Arrayable as ArrayableContract;
use Viserio\Contract\Support\Jsonable as JsonableContract;
use Viserio\Contract\Support\Stringable as StringableContract;
use function count;

abstract class AbstractPaginator implements ArrayableContract,
    ArrayAccess,
    Countable,
    IteratorAggregate,
    JsonableContract,
    JsonSerializable,
    PaginatorContract,
    StringableContract
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
     * @var null|string
     */
    protected $fragment;

    /**
     * The query string variable used to store the page.
     *
     * @var string
     */
    protected $pageName = 'page';

    /**
     * The current page resolver callback.
     *
     * @var Closure
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
    public function __call(string $method, array $parameters)
    {
        return $this->items->{$method}(...$parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        try {
            return $this->render();
        } catch (Throwable $exception) {
            // Really, PHP? https://bugs.php.net/bug.php?id=53648
            \trigger_error(self::class . '::__toString exception: ' . (string) $exception, \E_USER_ERROR);

            return '';
        }
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
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function setPath(string $path): PaginatorContract
    {
        $this->path = $path !== '/' ? \rtrim($path, '/') : $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getFragment(): ?string
    {
        return $this->fragment;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function setFragment(string $fragment): PaginatorContract
    {
        $this->fragment = $fragment;

        return $this;
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
    public function setPageName(string $name): PaginatorContract
    {
        $this->pageName = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUrlRange(int $start, int $end): array
    {
        $urls = [];

        for ($page = $start; $page <= $end; $page++) {
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

        if (\count($this->query) > 0) {
            $parameters = \array_merge($this->query, $parameters);
        }

        $url = $this->path;
        $url .= (\strpos($this->path, '?') !== false ? '&' : '?');
        $url .= \http_build_query($parameters, '', '&');
        $url .= $this->buildFragment();

        return $url;
    }

    /**
     * {@inheritdoc}
     */
    public function getPreviousPageUrl(): ?string
    {
        if ($this->getCurrentPage() > 1) {
            return $this->getUrl($this->getCurrentPage() - 1);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function addQuery(string $key, string $value): PaginatorContract
    {
        if ($this->pageName !== $key) {
            $this->query[$key] = $value;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function appends($key, ?string $value = null): PaginatorContract
    {
        if (\is_array($key)) {
            return $this->appendArray($key);
        }

        return $this->addQuery($key, $value);
    }

    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator
     *
     * @codeCoverageIgnore
     */
    public function getIterator(): ArrayIterator
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
    public function count(): int
    {
        return $this->items->count();
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstItem(): int
    {
        if (\count($this->items) === 0) {
            return 0;
        }

        return ($this->currentPage - 1) * $this->itemCountPerPage + 1;
    }

    /**
     * {@inheritdoc}
     */
    public function getLastItem(): int
    {
        if (\count($this->items) === 0) {
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
     */
    public function hasPages(): bool
    {
        return ! ($this->getCurrentPage() === 1 && ! $this->hasMorePages());
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
     * Determine if the given item exist.
     *
     * @param mixed $key
     *
     * @return bool
     *
     * @codeCoverageIgnore
     */
    public function offsetExists($key): bool
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
    public function offsetSet($key, $value): void
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
    public function offsetUnset($key): void
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
        return $page >= 1 && \filter_var($page, \FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Resolve the current page or return the default value.
     *
     * @return int
     */
    protected function resolveCurrentPage(): int
    {
        $query = $this->request->getQueryParams();

        if (\array_key_exists($this->pageName, $query)) {
            $query = $this->secureInput($query);
            $page = $query[$this->pageName];

            if ((int) $page >= 1 && \filter_var($page, \FILTER_VALIDATE_INT) !== false) {
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
        $secure = static function (&$v): void {
            if (! \is_string($v) && ! \is_numeric($v)) {
                $v = '';
            } elseif (\strpos($v, "\0") !== false) {
                $v = '';
            } elseif (! \mb_check_encoding($v, 'UTF-8')) {
                $v = '';
            }
        };

        \array_walk_recursive($query, $secure);

        return $query;
    }
}
