<?php
declare(strict_types=1);
namespace Viserio\Component\WebProfiler\DataCollectors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\WebProfiler\AssetAware as AssetAwareContract;
use Viserio\Component\Contracts\WebProfiler\MenuAware as MenuAwareContract;
use Viserio\Component\Contracts\WebProfiler\TooltipAware as TooltipAwareContract;

class AjaxRequestsDataCollector extends AbstractDataCollector implements
    AssetAwareContract,
    TooltipAwareContract,
    MenuAwareContract
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
    public function getTooltip(): string
    {
        return $this->createTooltipGroup([
            '0 AJAX requests' => '',
            '<table class="webprofiler-ajax-requests">
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>URL</th>
                        <th>Time</th>
                        <th>Profile</th>
                    </tr>
                </thead>
                <tbody class="webprofiler-ajax-request-list"></tbody>
            </table>',
        ]);
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
