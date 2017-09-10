<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\DataCollector\Bridge\Cache;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use Viserio\Component\Contract\Profiler\Exception\UnexpectedValueException;
use Viserio\Component\Contract\Profiler\PanelAware as PanelAwareContract;
use Viserio\Component\Contract\Profiler\TooltipAware as TooltipAwareContract;
use Viserio\Component\Profiler\DataCollector\AbstractDataCollector;

/**
 * Ported from.
 *
 * @see https://github.com/php-cache/cache-bundle/blob/master/src/DataCollector/CacheDataCollector.php
 */
class Psr6Psr16CacheDataCollector extends AbstractDataCollector implements
    TooltipAwareContract,
    PanelAwareContract
{
    /**
     * Collection of TraceableCacheItemDecorater.
     *
     * @var array
     */
    private $pools = [];

    /**
     * Create a new cache data collector.
     *
     * @param \Viserio\Component\Profiler\DataCollector\Bridge\Cache\PhpCacheTraceableCacheDecorator|\Viserio\Component\Profiler\DataCollector\Bridge\Cache\SimpleTraceableCacheDecorator|\Viserio\Component\Profiler\DataCollector\Bridge\Cache\TraceableCacheItemDecorator $cache
     *
     * @throws \Viserio\Component\Contract\Profiler\Exception\UnexpectedValueException
     *
     * @return void
     */
    public function addPool($cache): void
    {
        if ($cache instanceof TraceableCacheItemDecorator ||
            $cache instanceof SimpleTraceableCacheDecorator ||
            $cache instanceof PhpCacheTraceableCacheDecorator
        ) {
            $this->pools[$cache->getName()] = $cache;

            return;
        }

        throw new UnexpectedValueException(\sprintf(
            'The object [%s] must be an instance of [%s] or [%s].',
            \get_class($cache),
            TraceableCacheItemDecorator::class,
            SimpleTraceableCacheDecorator::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void
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
            'icon'  => 'ic_layers_white_24px.svg',
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
        $data = [];

        foreach ($this->data['pools']['calls'] as $name => $calls) {
            $html              = '';
            $statistic         = $this->data['pools']['statistics'][$name];
            $statistic['time'] = $this->formatDuration($statistic['time']);

            $html .= $this->createMetrics(
                $statistic,
                'Statistics'
            );

            $calledCalls = [];
            foreach ($calls as $i => $call) {
                $calledCalls[] = [
                    $this->formatDuration($call->end - $call->start),
                    $call->name,
                    $call->result,
                ];
            }

            $html .= $this->createTable(
                $calledCalls,
                [
                    'name'    => 'Calls',
                    'headers' => ['Time', 'Call', 'Hit'],
                ]
            );

            $data[] = [
                'name'    => (new ReflectionClass($name))->getShortName(),
                'content' => $html,
            ];
        }

        return $this->createTabs($data);
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
                $statistics[$name]['time'] += $call->end - $call->start;

                if ($call->name === 'getItem') {
                    $statistics[$name]['reads'] += 1;

                    if ($call->hits) {
                        $statistics[$name]['hits'] += 1;
                    } else {
                        $statistics[$name]['misses'] += 1;
                    }
                } elseif ($call->name === 'getItems') {
                    $count = $call->hits + $call->misses;
                    $statistics[$name]['reads'] += $count;
                    $statistics[$name]['hits'] += $call->hits;
                    $statistics[$name]['misses'] += $count - $call->misses;
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
                $statistics[$name]['hits'] =
                    \round(100 * $statistics[$name]['hits'] / $statistics[$name]['reads'], 2) . '%';
            } else {
                $statistics[$name]['hits'] = 0;
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
            $totals['hits'] = \round(100 * $totals['hits'] / $totals['reads'], 2) . '%';
        } else {
            $totals['hits'] = 0;
        }

        return $totals;
    }
}
