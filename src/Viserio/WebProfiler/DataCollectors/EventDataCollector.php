<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Viserio\Contracts\WebProfiler\PanelAware as PanelAwareContract;
use Viserio\Contracts\WebProfiler\TabAware as TabAwareContract;

class EventDataCollector extends AbstractDataCollector implements TabAwareContract, PanelAwareContract
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
    public function getName(): string
    {
        return 'events';
    }

    /**
     * {@inheritdoc}
     */
    public function getTabPosition(): string
    {
        return 'left';
    }

    /**
     * {@inheritdoc}
     */
    public function getTab(): array
    {
        return [
            'icon' => file_get_contents(__DIR__ . '/../Resources/icons/ic_settings_applications_white_24px.svg'),
            'label' => 'Events',
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
