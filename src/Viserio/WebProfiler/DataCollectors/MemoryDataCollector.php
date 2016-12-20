<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\WebProfiler\MenuAware as MenuAwareContract;
use Viserio\Contracts\WebProfiler\TooltipAware as TooltipAwareContract;

class MemoryDataCollector extends AbstractDataCollector implements TooltipAwareContract, MenuAwareContract
{
    /**
     * Collected data.
     *
     * @var array
     */
    protected $data;

    /**
     * Create new memory data collector.
     */
    public function __construct()
    {
        $this->data = [
            'memory'       => 0,
            'memory_limit' => $this->convertToBytes(ini_get('memory_limit')),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
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
            'icon'  => file_get_contents(__DIR__ . '/../Resources/icons/ic_memory_white_24px.svg'),
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
        $limit = $this->data['memory_limit'] == -1 ? 'Unlimited' : $this->data['memory_limit'] / 1024 / 1024;

        return $this->createTooltipGroup([
            'Peak memory usage' => $this->data['memory'] / 1024 / 1024 . ' MB',
            'PHP memory limit'  => $limit . ' MB',
        ]);
    }

    /**
     * Updates the memory usage data.
     */
    public function updateMemoryUsage()
    {
        $this->data['memory'] = memory_get_peak_usage(true);
    }
}
