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

namespace Viserio\Contract\Pagination;

interface Paginator
{
    /**
     * Render the pagination using the given view.
     */
    public function render(?string $view = null): string;

    /**
     * Create a range of pagination URLs.
     */
    public function getUrlRange(int $start, int $end): array;

    /**
     * Get the URL for the next page.
     */
    public function getNextPageUrl(): ?string;

    /**
     * Get the URL for a given page number.
     */
    public function getUrl(int $page): string;

    /**
     * Get the URL for the previous page.
     */
    public function getPreviousPageUrl(): ?string;

    /**
     * Add a query string value to the paginator.
     */
    public function addQuery(string $key, string $value): self;

    /**
     * Set the URL fragment to be appended to URLs.
     */
    public function setFragment(string $fragment): self;

    /**
     * Get the URL fragment to be appended to URLs.
     */
    public function getFragment(): ?string;

    /**
     * Add a set of query string values to the paginator.
     *
     * @param array|string $key
     */
    public function appends($key, ?string $value = null): self;

    /**
     * Get the query string variable used to store the page.
     */
    public function getPageName(): string;

    /**
     * Set the query string variable used to store the page.
     */
    public function setPageName(string $name): self;

    /**
     * Set the base path to assign to all URLs.
     */
    public function setPath(string $path): self;

    /**
     * Gets the base path.
     */
    public function getPath(): string;

    /**
     * Determine if the list of items is empty or not.
     */
    public function isEmpty(): bool;

    /**
     * Get the slice of items being paginated.
     */
    public function getItems(): array;

    /**
     * Get the number of the first item in the slice.
     */
    public function getFirstItem(): int;

    /**
     * Get the number of the last item in the slice.
     */
    public function getLastItem(): int;

    /**
     * Determine if the pagination is on the first page.
     */
    public function onFirstPage(): bool;

    /**
     * Get the number of items shown per page.
     */
    public function getItemsPerPage(): int;

    /**
     * Get the current page.
     */
    public function getCurrentPage(): int;

    /**
     * Determine if there are enough items to split into multiple pages.
     */
    public function hasPages(): bool;

    /**
     * Determine if there are more items in the data source.
     */
    public function hasMorePages(): bool;
}
