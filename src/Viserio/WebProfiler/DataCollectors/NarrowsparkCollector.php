<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors;

use Viserio\Contracts\WebProfiler\DataCollector as DataCollectorContract;
use Viserio\Contracts\WebProfiler\TabAware as TabAwareContract;
use Viserio\Contracts\WebProfiler\TooltipAware as TooltipAwareContract;
use Viserio\Foundation\Application;

class NarrowsparkCollector implements TooltipAwareContract, TabAwareContract, DataCollectorContract
{
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
        $hasXdebug = extension_loaded('xdebug') ? 'status-green' : 'status-red';

        $tooltip = '<div class="webprofiler-tab-tooltip-group">';
        $tooltip .= '<b>Profiler token</b><span>teste</span> <br>';
        $tooltip .= '<b>Application name</b><span></span> <br>';
        $tooltip .= '<b>Environment</b><span>' . env('APP_ENV', 'develop') . '</span> <br>';
        $tooltip .= '</div>';
        // php infos
        $tooltip .= '<div class="webprofiler-tab-tooltip-group">';
        $tooltip .= '<b>PHP version</b><span>' . phpversion() . '</span> <br>';
        $tooltip .= '<b>PHP Extensions</b><span class="' . $hasXdebug . '">xdebug</span> <br>';
        $tooltip .= '<b>PHP SAPI</b><span>' . php_sapi_name() . '</span> <br>';
        $tooltip .= '</div>';

        $tooltip .= '<div class="webprofiler-tab-tooltip-group">';
        $tooltip .= '<b>Resources</b><span>teste</span> <br>';
        $tooltip .= '<b>Help</b><span></span> <br>';
        $tooltip .= '</div>';

        return $tooltip;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'narrowspark';
    }
}
