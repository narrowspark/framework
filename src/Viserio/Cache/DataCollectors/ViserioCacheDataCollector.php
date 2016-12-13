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

class ViserioCacheDataCollector extends AbstractDataCollector implements MenuAwareContract, TooltipAwareContract, PanelAwareContract, AssetAwareContract
{
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
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltip(): string
    {
        return '';
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
            'css' => __DIR__ . '/Resources/css/widgets/cache.css',
        ];
    }
}
