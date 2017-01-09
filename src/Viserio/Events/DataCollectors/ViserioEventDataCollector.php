<?php
declare(strict_types=1);
namespace Viserio\Events\DataCollectors;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Events\Event as EventContract;
use Viserio\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Contracts\WebProfiler\PanelAware as PanelAwareContract;
use Viserio\WebProfiler\DataCollectors\TimeDataCollector;

class ViserioEventDataCollector extends TimeDataCollector implements PanelAwareContract
{
    use EventsAwareTrait;

    /**
     * Create new events collector instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     * @param \Viserio\Contracts\Events\EventManager   $events
     */
    public function __construct(ServerRequestInterface $serverRequest, EventManagerContract $events)
    {
        parent::__construct($serverRequest);

        $this->events = $events;
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
    public function collect(ServerRequestInterface $serverRequest, ResponseInterface $response)
    {
        parent::collect($serverRequest, $response);

        $this->events->attach('#', function (EventContract $event) {
            $time = microtime(true);

            $this->addMeasure($event->getName(), $time, $time, $event->getParams());
        });

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
