<?php
declare(strict_types=1);
namespace Viserio\Component\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\WebProfiler\AssetAware as AssetAwareContract;
use Viserio\Component\WebProfiler\DataCollectors\AbstractDataCollector;

class RoutesCollector extends AbstractDataCollector implements AssetAwareContract
{
    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
        // all collecting is done client side
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'icon'  => 'ic_repeat_white_24px.svg',
            'label' => '0',
            'value' => '',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAssets(): array
    {
        return [
            'css' => __DIR__ . '/../Resources/css/ajax-requests.css',
            'js'  => __DIR__ . '/../Resources/js/ajaxHandler.js',
        ];
    }
}
