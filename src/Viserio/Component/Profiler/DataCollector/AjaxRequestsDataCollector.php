<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Profiler\DataCollector;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contract\Profiler\AssetAware as AssetAwareContract;
use Viserio\Contract\Profiler\TooltipAware as TooltipAwareContract;

class AjaxRequestsDataCollector extends AbstractDataCollector implements AssetAwareContract,
    TooltipAwareContract
{
    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void
    {
        // all collecting is done client side
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'icon' => 'ic_repeat_white_24px.svg',
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
            '<table class="profiler-ajax-requests">
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
                <tbody class="profiler-ajax-request-list"></tbody>
            </table>',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAssets(): array
    {
        return [
            'css' => \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'css' . \DIRECTORY_SEPARATOR . 'ajax-requests.css',
            'js' => \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'js' . \DIRECTORY_SEPARATOR . 'ajaxHandler.js',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        // all collecting is done client side
    }
}
