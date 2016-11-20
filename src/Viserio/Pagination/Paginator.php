<?php
declare(strict_types=1);
namespace Viserio\Pagination;

use Narrowspark\Collection\Collection;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Pagination\Adapter as AdapterContract;
use Viserio\Contracts\Pagination\Presenter as PresenterContract;
use Viserio\Contracts\View\Traits\ViewAwareTrait;
use Viserio\Pagination\Presenters\Bootstrap3;
use Viserio\Pagination\Presenters\Bootstrap4;
use Viserio\Pagination\Presenters\Foundation5;
use Viserio\Pagination\Presenters\SimplePagination;

class Paginator extends AbstractPaginator
{
    use ViewAwareTrait;

    /**
     * All pagination presenters.
     *
     * @var array
     */
    protected $presenters = [
        'bootstrap3' => Bootstrap3::class,
        'bootstrap4' => Bootstrap4::class,
        'foundation5' => Foundation5::class,
        'simple' => SimplePagination::class,
    ];

    /**
     * The default pagination presenter.
     *
     * @var string
     */
    protected $presenter = 'simple';

    /**
     * Create a new paginator.
     *
     * @param \Viserio\Contracts\Pagination\Adapter    $adapter
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function __construct(AdapterContract $adapter, ServerRequestInterface $request)
    {
        $this->request = $request;
        $this->setPath($this->request->getUri()->getPath());

        $this->items = new Collection($adapter->getItems());
        $this->itemCountPerPage = $adapter->getItemsPerPage();
        $this->currentPage = $this->getCurrentPage();

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
        if ($this->hasMorePages()) {
            return $this->getUrl($this->getCurrentPage() + 1);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function render(string $view = null): string
    {
        if (is_string($view)) {
            if ($this->views !== null && !isset($this->presenters[$view])) {
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
     * Manually indicate that the paginator does have more pages.
     *
     * @param bool $value
     *
     * @return $this
     */
    public function hasMorePagesWhen(bool $value = true)
    {
        $this->hasMore = $value;

        return $this;
    }

    /**
     * Determine if there are more items in the data source.
     *
     * @return bool
     */
    public function hasMorePages(): bool
    {
        return $this->hasMore;
    }

    /**
     * {@inheritdoc}
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
