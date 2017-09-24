<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\DataCollector;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

class TimeDataCollector extends AbstractDataCollector
{
    /**
     * The request start time.
     *
     * @var float
     */
    protected $requestStartTime;

    /**
     * The request end time.
     *
     * @var float
     */
    protected $requestEndTime;

    /**
     * Collection of started measures.
     *
     * @var array
     */
    protected $startedMeasures = [];

    /**
     * Collection of measures.
     *
     * @var array
     */
    protected $measures = [];

    /**
     * Create new time collector instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     */
    public function __construct(ServerRequestInterface $serverRequest)
    {
        $time = \microtime(true);

        if (($requestTimeFloat = $serverRequest->getHeaderLine('REQUEST_TIME_FLOAT')) !== '') {
            $time = $requestTimeFloat;
        } elseif (($requestTime = $serverRequest->getHeaderLine('REQUEST_TIME')) !== '') {
            $time = $requestTime;
        }

        $this->requestStartTime = (float) $time;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void
    {
        $this->requestEndTime = \microtime(true);

        foreach (\array_keys($this->startedMeasures) as $name) {
            $this->stopMeasure($name);
        }

        \usort($this->measures, function ($a, $b) {
            if ($a['start'] == $b['start']) {
                return 0;
            }

            return $a['start'] < $b['start'] ? -1 : 1;
        });

        $this->data = [
            'start'        => $this->requestStartTime,
            'end'          => $this->requestEndTime,
            'duration'     => $this->getRequestDuration(),
            'duration_str' => $this->formatDuration($this->getRequestDuration()),
            'measures'     => $this->measures,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'icon'  => 'ic_schedule_white_24px.svg',
            'label' => '',
            'value' => $this->data['duration_str'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getMenuPosition(): string
    {
        return 'right';
    }

    /**
     * Returns the duration of a request.
     *
     * @return float
     */
    public function getRequestDuration(): float
    {
        if ($this->requestEndTime !== null) {
            return $this->requestEndTime - $this->requestStartTime;
        }

        return \microtime(true) - $this->requestStartTime;
    }

    /**
     * Starts a measure.
     *
     * @param string      $name      Internal name, used to stop the measure
     * @param null|string $label     Public name
     * @param null|string $collector The source of the collector
     *
     * @return void
     */
    public function startMeasure(string $name, string $label = null, string $collector = null): void
    {
        $start = \microtime(true);

        $this->startedMeasures[$name] = [
            'label'     => $label ?? $name,
            'start'     => $start,
            'collector' => $collector,
        ];
    }

    /**
     * Check a measure exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasStartedMeasure(string $name): bool
    {
        return isset($this->startedMeasures[$name]);
    }

    /**
     * Stops a measure.
     *
     * @param string $name
     * @param array  $params
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    public function stopMeasure(string $name, array $params = []): void
    {
        $end = \microtime(true);

        if (! $this->hasStartedMeasure($name)) {
            throw new RuntimeException(\sprintf(
                'Failed stopping measure [%s] because it hasn\'t been started.',
                $name
            ));
        }

        $this->addMeasure(
            $this->startedMeasures[$name]['label'],
            $this->startedMeasures[$name]['start'],
            $end,
            $params,
            $this->startedMeasures[$name]['collector']
        );

        unset($this->startedMeasures[$name]);
    }

    /**
     * Adds a measure.
     *
     * @param string      $label
     * @param float       $start
     * @param float       $end
     * @param array       $params
     * @param null|string $collector
     *
     * @return void
     */
    public function addMeasure(
        string $label,
        float $start,
        float $end,
        array $params = [],
        ?string $collector = null
    ): void {
        $this->measures[] = [
            'label'          => $label,
            'start'          => $start,
            'relative_start' => $start - $this->requestStartTime,
            'end'            => $end,
            'relative_end'   => $end - $this->requestEndTime,
            'duration'       => $end - $start,
            'duration_str'   => $this->formatDuration($end - $start),
            'params'         => $params,
            'collector'      => $collector,
        ];
    }

    /**
     * Returns an array of all measures.
     *
     * @return array
     */
    public function getMeasures(): array
    {
        return $this->measures;
    }

    /**
     * Returns the request start time.
     *
     * @return float
     *
     * @codeCoverageIgnore
     */
    public function getRequestStartTime(): float
    {
        return $this->requestStartTime;
    }

    /**
     * Returns the request end time.
     *
     * @return float
     *
     * @codeCoverageIgnore
     */
    public function getRequestEndTime(): float
    {
        return $this->requestEndTime;
    }
}
