<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Viserio\Contracts\WebProfiler\TabAware as TabAwareContract;
use Viserio\Contracts\WebProfiler\TooltipAware as TooltipAwareContract;
use Viserio\Foundation\Application;

class NarrowsparkDataCollector extends AbstractDataCollector implements TooltipAwareContract, TabAwareContract
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
    public function getName(): string
    {
        return 'narrowspark';
    }

    /**
     * {@inheritdoc}
     */
    public function getTabPosition(): string
    {
        return 'right';
    }

    /**
     * {@inheritdoc}
     */
    public function getTab(): array
    {
        return [
            'icon' => file_get_contents(__DIR__ . '/../Resources/icons/ic_memory_white_24px.svg'),
            'label' => '',
            'value' => Application::VERSION,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltip(): string
    {
        $debug = env('APP_DEBUG', 'false');

        $tooltip = $this->createTooltipGroup([
            'Profiler token' => '',
            'Application name' => '',
            'Environment' => env('APP_ENV', 'develop'),
            'Debug' => [
                [
                    'class' => $debug !== 'false' ? 'status-green' : 'status-red',
                    'value' => $debug !== 'false' ? 'enabled' : 'disabled',
                ],
            ]
        ]);

        $tooltip .= $this->createTooltipGroup([
            'PHP version' => phpversion(),
            'PHP Extensions' => [
                [
                    'class' => extension_loaded('xdebug') ? 'status-green' : 'status-red',
                    'value' => 'xdebug',
                ],
            ],
            'PHP SAPI' => php_sapi_name(),
        ]);

        $tooltip .= $this->createTooltipGroup([
            'Resources' => '',
            'Help' => '',
        ]);

        return $tooltip;
    }
}
