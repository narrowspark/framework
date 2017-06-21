<?php
declare(strict_types=1);
namespace Viserio\Component\Events\DataCollector;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Profiler\PanelAware as PanelAwareContract;
use Viserio\Component\Profiler\DataCollector\AbstractDataCollector;

class ViserioEventsDataCollector extends AbstractDataCollector implements PanelAwareContract
{
    /**
     * Create new events collector instance.
     *
     * @param \Viserio\Component\Events\DataCollector\TraceableEventManager $eventManager
     */
    public function __construct(TraceableEventManager $eventManager)
    {
        $this->eventManager = $eventManager;
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
            'value' => '',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPanel(): string
    {
        $calledContent = $notCalledContent = '';
        $called        = $notCalled        = [];

        $tableConfig = function (string $name): array {
            return ['name' => $name, 'headers' => ['Priority', 'Listener'], 'vardumper' => false];
        };

        foreach ($this->eventManager->getCalledListeners() as $eventName => $calledListener) {
            foreach ($calledListener as $listner) {
                $called[] = [$listner['priority'], $listner['pretty']];
            }

            $calledContent .= $this->createTable($called, $tableConfig($eventName));
        }

        foreach ($this->eventManager->getNotCalledListeners() as $eventName => $calledListener) {
            foreach ($calledListener as $listner) {
                $notCalled[] = [$listner['priority'], $listner['pretty']];
            }

            $notCalledContent .= $this->createTable($notCalled, $tableConfig($eventName));
        }

        return $this->createTabs([
            [
                'name'    => 'Called Listeners <span class="counter">' . count($called) . '</span>',
                'content' => $calledContent,
            ],
            [
                'name'    => 'Not Called Listeners <span class="counter">' . count($notCalled) . '</span>',
                'content' => $notCalledContent,
            ],
        ]);
    }
}
