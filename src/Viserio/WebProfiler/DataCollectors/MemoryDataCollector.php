<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors;

use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\WebProfiler\LateDataCollector as LateDataCollectorContract;
use Viserio\Contracts\WebProfiler\TabAware as TabAwareContract;
use Viserio\Contracts\WebProfiler\TooltipAware as TooltipAwareContract;

class MemoryDataCollector extends AbstractDataCollector implements TooltipAwareContract, TabAwareContract, LateDataCollectorContract
{
    /**
     * Collected data.
     *
     * @var array
     */
    protected $data;

    public function __construct()
    {
        $this->data = [
            'memory' => 0,
            'memory_limit' => $this->convertToBytes(ini_get('memory_limit')),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'memory';
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
        $memory = $this->data['memory'] / 1024 / 1024;

        return [
            'icon' => file_get_contents(__DIR__ . '/../Resources/icons/ic_memory_white_24px.svg'),
            'label' => $memory,
            'value' => 'MB',
            'class' => $memory > 50 ? 'yellow' : '',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltip(): string
    {
        $tooltip = '<div class="webprofiler-tab-tooltip-group">';
        $tooltip .= '<b>Peak memory usage</b><span>' . $this->data['memory'] / 1024 / 1024 . '</span> <br>';
        $tooltip .= '<b>PHP memory limit</b><span>' . ($this->data['memory_limit'] == -1 ? 'Unlimited' : $this->data['memory_limit'] / 1024 / 1024) . ' MB</span> <br>';
        $tooltip .= '</div>';

        return $tooltip;
    }

    /**
     * {@inheritdoc}
     */
    public function lateCollect()
    {
        $this->data['memory'] = memory_get_peak_usage(true);
    }

    /**
     * Convert a number string to bytes.
     *
     * @param string
     *
     * @return int
     */
    private function convertToBytes(string $memoryLimit): int
    {
        if ($memoryLimit === '-1') {
            return -1;
        }

        $memoryLimit = strtolower($memoryLimit);
        $max = strtolower(ltrim($memoryLimit, '+'));

        if (0 === strpos($max, '0x')) {
            $max = intval($max, 16);
        } elseif (0 === strpos($max, '0')) {
            $max = intval($max, 8);
        } else {
            $max = (int) $max;
        }

        switch (substr($memoryLimit, -1)) {
            case 't': $max *= 1024;
            case 'g': $max *= 1024;
            case 'm': $max *= 1024;
            case 'k': $max *= 1024;
        }

        return $max;
    }
}
