<?php
declare(strict_types=1);
namespace Viserio\Component\Events\DataCollector;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Events\Event as EventContract;
use Viserio\Component\Contracts\Profiler\PanelAware as PanelAwareContract;
use Viserio\Component\Profiler\DataCollector\TimeDataCollector;

class ViserioEventsDataCollector extends TimeDataCollector implements PanelAwareContract
{
    /**
     * Create new events collector instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     */
    public function __construct(ServerRequestInterface $serverRequest)
    {
        parent::__construct($serverRequest);
    }

    public function subscribe(EventContract $event): void
    {
        $time = microtime(true);

        $this->addMeasure($event->getName(), $time, $time, $event->getParams());
    }

    /**
     * {@inheritdoc}
     */
    public function getMenuPosition(): string
    {
        return 'left';
    }

    /**
     * {@inheritdoc}
     */
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response): void
    {
        parent::collect($serverRequest, $response);

        $this->data['events'] = count($this->data['measures']);
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'icon'  => file_get_contents(__DIR__ . '/Resources/icons/ic_filter_list_white_24px.svg'),
            'label' => 'Events',
            'value' => $this->data['events'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        $html = '';

        return $html;
    }
}
