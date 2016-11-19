<?php
declare(strict_types=1);
namespace Viserio\Pagination;

use Narrowspark\Collection\Collection;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Pagination\Presenter as PresenterContract;
use Viserio\Contracts\Pagination\Adapter as AdapterContract;
use Viserio\Contracts\View\Traits\ViewAwareTrait;
use Viserio\Pagination\Presenters\Bootstrap3;
use Viserio\Pagination\Presenters\Bootstrap4;
use Viserio\Pagination\Presenters\Foundation5;

class Paginator extends AbstractPaginator
{
    use ViewAwareTrait;

    /**
     * All pagination presenters.
     *
     * @var array
     */
    protected $presenters = [
        'bootstrap3'  => Bootstrap3::class,
        'bootstrap4'  => Bootstrap4::class,
        'foundation5' => Foundation5::class,
    ];

    /**
     * The total number of items before slicing.
     *
     * @var int
     */
    protected $total;

    /**
     * The last available page.
     *
     * @var int
     */
    protected $lastPage;

    /**
     * The default pagination presenter.
     *
     * @var string
     */
    protected $presenter = 'bootstrap3';

    /**
     * Create a new paginator.
     *
     * @param \Viserio\Contracts\Pagination\Adapter    $adapter
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function __construct(AdapterContract $adapter, ServerRequestInterface $request)
    {
        $this->request = $request;
        $this->path = (string) $this->request->getUri();

        $this->items = new Collection($adapter->getItems());
        $this->itemCountPerPage = $adapter->getItemsPerPage();
        $this->currentPage = $this->getCurrentPage();
        $this->path = $this->path != '/' ? rtrim($this->path, '/') : $this->path;

        $this->checkForMorePages();
    }

    /**
     * Set a default presenter.
     *
     * @param string $presenter
     *
     * @return $this
     */
    public function setDefaultPresenter(string $presenter)
    {
        $this->presenter = $presenter;

        return $this;
    }

    /**
     * Get the default presenter.
     *
     * @return string
     */
    public function getDefaultPresenter(): string
    {
        return $this->presenter;
    }

    /**
     * Get the URL for the next page.
     *
     * @return string|null
     */
    public function getNextPageUrl()
    {
        if ($this->getLastPage() > $this->getCurrentPage()) {
            return $this->getUrl($this->getCurrentPage() + 1);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function render(string $view = null): string
    {
        if (is_string($view)) {
            if ($this->view !== null && !isset($this->presenters[$view])) {
                return $this->getViewFactory()->create($view, ['paginator' => $this]);
            } elseif (isset($this->presenters[$view])) {
                return (new $this->presenters[$view]($this))->render();
            }
        }

        return (new $this->presenters[$this->getDefaultPresenter()]($this))->render();
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'per_page' => $this->getItemsPerPage(),
            'current_page' => $this->getCurrentPage(),
            'next_page_url' => $this->getNextPageUrl(),
            'prev_page_url' => $this->getPreviousPageUrl(),
            'from' => $this->getFirstItem(),
            'to' => $this->getLastItem(),
            'data' => $this->items->toArray(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Determine if there are more items in the data source.
     *
     * @return bool
     */
    public function hasMorePages(): bool
    {
        return $this->getCurrentPage() < $this->getLastPage();
    }

    /**
     * Get the last page.
     *
     * @return int
     */
    public function getLastPage(): int
    {
        return $this->lastPage;
    }

    /**
     * Get the total number of items being paginated.
     *
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->total;
    }

    /**
     * Get the current page for the request.
     *
     * @return int
     */
    public function getCurrentPage(): int
    {
        $currentPage = $this->resolveCurrentPage();

        return $this->isValidPageNumber($currentPage) ? (int) $currentPage : 1;
    }

    /**
     * Check for more pages. The last item will be sliced off.
     */
    protected function checkForMorePages()
    {
        $this->hasMore = count($this->items) > ($this->itemCountPerPage);

        $this->items = $this->items->slice(0, $this->itemCountPerPage);
    }
}
