<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors\Bridge\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Viserio\Contracts\WebProfiler\MenuAware as MenuAwareContract;
use Viserio\Contracts\WebProfiler\PanelAware as PanelAwareContract;
use Viserio\Contracts\WebProfiler\TooltipAware as TooltipAwareContract;
use Viserio\WebProfiler\DataCollectors\AbstractDataCollector;

/**
 * Ported from.
 *
 * @link https://github.com/php-cache/cache-bundle/blob/master/src/DataCollector/CacheDataCollector.php
 */
class Psr6CacheDataCollector extends AbstractDataCollector implements
    MenuAwareContract,
    TooltipAwareContract,
    PanelAwareContract
{
    /**
     * Collection of CacheItemPoolInterfaces.
     *
     * @var \Psr\Cache\CacheItemPoolInterface[]
     */
    private $pools = [];

    /**
     * Stopwatch instance.
     *
     * @var \Symfony\Component\Stopwatch\Stopwatch
     */
    private $stopwatch;

    /**
     * Create a new Psr6CacheDataCollector instance.
     *
     * @param \Symfony\Component\Stopwatch\Stopwatch $stopwatch
     */
    public function __construct(Stopwatch $stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }

    /**
     * Create a new cache data collector.
     *
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     */
    public function addPool(CacheItemPoolInterface $cache)
    {
        $this->pools[get_class($cache)] = new TraceableCacheItemDecorater($cache, $this->stopwatch);
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
        $empty = [
            'calls'      => [],
            'config'     => [],
            'options'    => [],
            'statistics' => [],
        ];

        $this->data = ['pools' => $empty, 'total' => $empty];

        foreach ($this->pools as $name => $instance) {
            $this->data['pools']['calls'][$name] = $instance->getCalls();
        }

        $this->data['pools']['statistics'] = $this->calculateStatistics();
        $this->data['total']['statistics'] = $this->calculateTotalStatistics();
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        $static = $this->data['total']['statistics'];

        return [
            'icon'  => '',
            'label' => $static['calls'] . ' in',
            'value' => $this->formatDuration($static['time']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTooltip(): string
    {
        $static = $this->data['total']['statistics'];

        return $this->createTooltipGroup([
            'Cache calls'  => $static['calls'],
            'Total time'   => $this->formatDuration($static['time']),
            'Cache hits'   => $static['hits'],
            'Cache writes' => $static['writes'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        $html = '';

        foreach ($this->data['pools']['statistics'] as $key => $value) {
            $html .= $this->createTable(
                [array_values($value)],
                $key,
                ['Calls', 'Time', 'Reads', 'Hits', 'Misses', 'Writes', 'Deletes', 'Ratio']
            );
        }

        return $html;
    }

    /**
     * Method returns amount of logged Cache reads: "get" calls.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return $this->data['pools']['statistics'];
    }

    /**
     * Method returns the statistic totals.
     *
     * @return array
     */
    public function getTotals(): array
    {
        return $this->data['total']['statistics'];
    }

    /**
     * Method returns all logged Cache call objects.
     *
     * @return int
     */
    public function getCalls(): int
    {
        return $this->data['pools']['calls'];
    }

    /**
     * @return array
     */
    private function calculateStatistics(): array
    {
        $statistics = [];

        foreach ($this->data['pools']['calls'] as $name => $calls) {
            $statistics[$name] = [
                'calls'   => 0,
                'time'    => 0,
                'reads'   => 0,
                'hits'    => 0,
                'misses'  => 0,
                'writes'  => 0,
                'deletes' => 0,
            ];

            foreach ($calls as $call) {
                $statistics[$name]['calls'] += 1;
                $statistics[$name]['time'] += $call->time;

                if ($call->name === 'getItem') {
                    $statistics[$name]['reads'] += 1;

                    if ($call->isHit) {
                        $statistics[$name]['hits'] += 1;
                    } else {
                        $statistics[$name]['misses'] += 1;
                    }
                } elseif ($call->name === 'hasItem') {
                    $statistics[$name]['reads'] += 1;

                    if ($call->result === false) {
                        $statistics[$name]['misses'] += 1;
                    }
                } elseif ($call->name === 'save') {
                    $statistics[$name]['writes'] += 1;
                } elseif ($call->name === 'deleteItem') {
                    $statistics[$name]['deletes'] += 1;
                }
            }

            if ($statistics[$name]['reads']) {
                $statistics[$name]['ratio'] = round(100 * $statistics[$name]['hits'] / $statistics[$name]['reads'], 2) . '%';
            } else {
                $statistics[$name]['ratio'] = 'N/A';
            }
        }

        return $statistics;
    }

    /**
     * @return array
     */
    private function calculateTotalStatistics(): array
    {
        $statistics = $this->getStatistics();
        $totals     = [
            'calls'  => 0,
            'time'   => 0,
            'reads'  => 0,
            'hits'   => 0,
            'misses' => 0,
            'writes' => 0,
        ];

        foreach ($statistics as $name => $values) {
            foreach ($totals as $key => $value) {
                $totals[$key] += $statistics[$name][$key];
            }
        }

        if ($totals['reads']) {
            $totals['ratio'] = round(100 * $totals['hits'] / $totals['reads'], 2) . '%';
        } else {
            $totals['ratio'] = 'N/A';
        }

        return $totals;
    }
}
