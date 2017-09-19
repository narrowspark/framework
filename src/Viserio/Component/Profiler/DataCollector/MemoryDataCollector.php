<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\DataCollector;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Profiler\TooltipAware as TooltipAwareContract;

class MemoryDataCollector extends AbstractDataCollector implements TooltipAwareContract
{
    /**
     * Create new memory data collector.
     */
    public function __construct()
    {
        $memoryLimit = \ini_get('memory_limit');

        $this->data = [
            'memory'       => 0,
            'memory_limit' => $memoryLimit == '-1' ? -1 : self::convertToBytes($memoryLimit),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void
    {
        $this->updateMemoryUsage();
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        $memory = $this->data['memory'] / 1024 / 1024;

        return [
            'icon'  => 'ic_memory_white_24px.svg',
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
        $coverToMb = function (int $number) {
            return $number / 1024 / 1024;
        };

        $limit = $this->data['memory_limit'] == '-1' ? 'Unlimited' : $coverToMb($this->data['memory_limit']);

        return $this->createTooltipGroup([
            'Peak memory usage' => $coverToMb($this->data['memory']) . ' MB',
            'PHP memory limit'  => $limit . ' MB',
        ]);
    }

    /**
     * Updates the memory usage data.
     */
    public function updateMemoryUsage(): void
    {
        $this->data['memory'] = \memory_get_peak_usage(true);
    }
}
