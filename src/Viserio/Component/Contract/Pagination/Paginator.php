<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Pagination;

interface Paginator
{
    /**
     * Render the pagination using the given view.
     *
     * @param null|string $view
     *
     * @return string
     */
    public function render(?string $view = null): string;

    /**
     * Create a range of pagination URLs.
     *
     * @param int $start
     * @param int $end
     *
     * @return array
     */
    public function getUrlRange(int $start, int $end): array;

    /**
     * Get the URL for the next page.
     *
     * @return null|string
     */
    public function getNextPageUrl(): ?string;

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
     * @return null|string
     */
    public function getPreviousPageUrl(): ?string;

    /**
     * Add a query string value to the paginator.
     *
     * @param string $key
     * @param string $value
     *
     * @return \Viserio\Component\Contract\Pagination\Paginator
     */
    public function addQuery(string $key, string $value): self;

    /**
     * Set the URL fragment to be appended to URLs.
     *
     * @param string $fragment
     *
     * @return \Viserio\Component\Contract\Pagination\Paginator
     */
    public function setFragment(string $fragment): self;

    /**
     * Get the URL fragment to be appended to URLs.
     *
     * @return null|string
     */
    public function getFragment(): ?string;

    /**
     * Add a set of query string values to the paginator.
     *
     * @param array|string $key
     * @param null|string  $value
     *
     * @return \Viserio\Component\Contract\Pagination\Paginator
     */
    public function appends($key, string $value = null): self;

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
     * @return \Viserio\Component\Contract\Pagination\Paginator
     */
    public function setPageName(string $name): self;

    /**
     * Set the base path to assign to all URLs.
     *
     * @param string $path
     *
     * @return \Viserio\Component\Contract\Pagination\Paginator
     */
    public function setPath(string $path): self;

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
     * Determine if the pagination is on the first page.
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
