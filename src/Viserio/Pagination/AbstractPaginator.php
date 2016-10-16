<?php
declare(strict_types=1);
namespace Viserio\Pagination;

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
}
