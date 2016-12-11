<?php
declare(strict_types=1);
namespace Viserio\Foundation\DataCollectors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\WebProfiler\PanelAware as PanelAwareContract;
use Viserio\WebProfiler\DataCollectors\TimeDataCollector;

class ViserioEventDataCollector extends TimeDataCollector implements PanelAwareContract
{
    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'icon' => file_get_contents(__DIR__ . '/../Resources/icons/ic_settings_applications_white_24px.svg'),
            'label' => 'Events',
            'value' => '',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        $html = '';

        return $html;
    }
}
