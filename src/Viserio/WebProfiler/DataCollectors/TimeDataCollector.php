<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Viserio\Contracts\WebProfiler\MenuAware as MenuAwareContract;

class TimeDataCollector extends AbstractDataCollector implements MenuAwareContract
{
    /**
     * @var float
     */
    protected $requestStartTime;

    /**
     * @var float
     */
    protected $requestEndTime;

    /**
     * @var array
     */
    protected $startedMeasures = [];

    /**
     * @var array
     */
    protected $measures = [];

    /**
     * Create new time collector.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     */
    public function __construct(ServerRequestInterface $serverRequest)
    {
        if ($requestTimeFloat = $serverRequest->getHeaderLine('REQUEST_TIME_FLOAT') !== '') {
            $time = $requestTimeFloat;
        } elseif ($requestTime = $serverRequest->getHeaderLine('REQUEST_TIME') !== '') {
            $time = $requestTime;
        } else {
            $time = microtime(true);
        }

        $this->requestStartTime = $time;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
        $this->requestEndTime = microtime(true);

        foreach (array_keys($this->startedMeasures) as $name) {
            $this->stopMeasure($name);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'icon'  => 'ic_schedule_white_24px.svg',
            'label' => $this->formatDuration($this->getRequestDuration()),
            'value' => '',
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
    public function getRequestDuration()
    {
        if ($this->requestEndTime !== null) {
            return $this->requestEndTime - $this->requestStartTime;
        }

        return microtime(true) - $this->requestStartTime;
    }

    /**
     * Starts a measure.
     *
     * @param string      $name      Internal name, used to stop the measure
     * @param string|null $label     Public name
     * @param string|null $collector The source of the collector
     */
    public function startMeasure(string $name, string $label = null, string $collector = null)
    {
        $start = microtime(true);

        $this->startedMeasures[$name] = [
            'label'     => $label ?: $name,
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
     */
    public function stopMeasure(string $name, array $params = [])
    {
        $end = microtime(true);

        if (! $this->hasStartedMeasure($name)) {
            throw new RuntimeException(sprintf(
                'Failed stopping measure "%s" because it hasn\'t been started',
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
     */
    public function getRequestStartTime(): float
    {
        return $this->requestStartTime;
    }

    /**
     * Returns the request end time.
     *
     * @return float
     */
    public function getRequestEndTime(): float
    {
        return $this->requestEndTime;
    }
}
