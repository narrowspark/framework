<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Pagination;

interface Paginator
{
    /**
     * Render the paginator using the given view.
     *
     * @param \Viserio\Component\Contracts\Pagination\Presenter|string|null $view
     *
     * @return string
     */
    public function render(string $view = null): string;

    /**
     * Create a range of pagination URLs.
     *
     * @param int $start
     * @param int $end
     *
     * @return string
     */
    public function getUrlRange(int $start, int $end): string;

    /**
     * Get the URL for a given page number.
     *
     * @param int $page
     *
     * @return string
     */
    public function getUrl(int $page): string;

    /**
     * Get the URL for the previous page.
     *
     * @return string|null
     */
    public function getPreviousPageUrl();

    /**
     * Add a query string value to the paginator.
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function addQuery(string $key, string $value);

    /**
     * Set the URL fragment to be appended to URLs.
     *
     * @param string $fragment
     *
     * @return $this
     */
    public function setFragment(string $fragment);

    /**
     * Get the URL fragment to be appended to URLs.
     *
     * @return string|null
     */
    public function getFragment();

    /**
     * Add a set of query string values to the paginator.
     *
     * @param array|string $key
     * @param string|null  $value
     *
     * @return $this
     */
    public function appends($key, string $value = null);

    /**
     * Get the query string variable used to store the page.
     *
     * @return string
     */
    public function getPageName(): string;

    /**
     * Set the query string variable used to store the page.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setPageName(string $name);

    /**
     * Set the base path to assign to all URLs.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setPath(string $path);

    /**
     * Gets the base path.
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Determine if the list of items is empty or not.
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Get the slice of items being paginated.
     *
     * @return array
     */
    public function getItems(): array;

    /**
     * Get the number of the first item in the slice.
     *
     * @return int
     */
    public function getFirstItem(): int;

    /**
     * Get the number of the last item in the slice.
     *
     * @return int
     */
    public function getLastItem(): int;

    /**
     * Determine if the paginator is on the first page.
     *
     * @return bool
     */
    public function onFirstPage(): bool;

    /**
     * Get the number of items shown per page.
     *
     * @return int
     */
    public function getItemsPerPage(): int;

    /**
     * Get the current page.
     *
     * @return int
     */
    public function getCurrentPage(): int;

    /**
     * Determine if there are enough items to split into multiple pages.
     *
     * @return bool
     */
    public function hasPages(): bool;

    /**
     * Determine if there are more items in the data source.
     *
     * @return bool
     */
    public function hasMorePages(): bool;
}
