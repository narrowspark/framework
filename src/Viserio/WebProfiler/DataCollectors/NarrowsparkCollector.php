<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors;

use Viserio\Contracts\WebProfiler\TabAware as TabAwareContract;
use Viserio\Contracts\WebProfiler\TooltipAware as TooltipAwareContract;
use Viserio\Foundation\Application;

class NarrowsparkCollector extends AbstractDataCollector implements TooltipAwareContract, TabAwareContract
{
    /**
     * Normalized Version.
     *
     * @var string
     */
    protected $version;

    public function __construct(string $version)
    {
        $this->version = Application::VERSION;
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
            'label' => '',
            'value' => $this->version
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

    /**
     * {@inheritdoc}
     */
    public function collect(): array
    {
        return [
            'narrowspark' => 'test',
        ];
    }
}
