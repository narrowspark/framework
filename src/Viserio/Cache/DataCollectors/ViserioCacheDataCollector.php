<?php
declare(strict_types=1);
namespace Viserio\Cache\DataCollectors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\WebProfiler\AssetAware as AssetAwareContract;
use Viserio\Contracts\WebProfiler\MenuAware as MenuAwareContract;
use Viserio\Contracts\WebProfiler\PanelAware as PanelAwareContract;
use Viserio\Contracts\WebProfiler\TooltipAware as TooltipAwareContract;
use Viserio\WebProfiler\DataCollectors\AbstractDataCollector;
use Psr\Cache\CacheItemPoolInterface;

class ViserioCacheDataCollector extends AbstractDataCollector implements
    MenuAwareContract,
    TooltipAwareContract,
    PanelAwareContract,
    AssetAwareContract
{
    /**
     * Create a new cache data collector.
     *
     * @param string $cache
     */
    public function __construct(CacheItemPoolInterface $cache)
    {
        # code...
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'label' => 'cache',
            'value' => '',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltip(): string
    {
        return $this->createTooltipGroup([
            'Cache Calls' => '',
            'Cache hits' => '',
            'Cache writes' => '',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getAssets(): array
    {
        return [
            'css' => __DIR__ . '/Resources/css/widgets/viserio/cache.css',
        ];
    }
}
