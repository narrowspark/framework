<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Events\DataCollector;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Profiler\DataCollector\AbstractDataCollector;
use Viserio\Contract\Events\Traits\EventManagerAwareTrait;
use Viserio\Contract\Profiler\PanelAware as PanelAwareContract;

class ViserioEventsDataCollector extends AbstractDataCollector implements PanelAwareContract
{
    use EventManagerAwareTrait;

    /**
     * Create new events collector instance.
     *
     * @param \Viserio\Component\Events\DataCollector\TraceableEventManager $eventManager
     */
    public function __construct(TraceableEventManager $eventManager)
    {
        $this->eventManager = $eventManager;

        $this->data = [
            'called_listeners' => [],
            'not_called_listeners' => [],
            'orphaned_events' => [],
        ];
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
        $this->data = [
            'called_listeners' => $this->eventManager->getCalledListeners(),
            'not_called_listeners' => $this->eventManager->getNotCalledListeners(),
            'orphaned_events' => $this->eventManager->getOrphanedEvents(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getMenu(): array
    {
        return [
            'icon' => \file_get_contents(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'icons' . \DIRECTORY_SEPARATOR . 'ic_filter_list_white_24px.svg'),
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
        $called = $notCalled = [];

        $tableConfig = static function (string $name, string $emptyText): array {
            return ['name' => $name, 'headers' => ['Priority', 'Listener'], 'vardumper' => false, 'empty_text' => $emptyText];
        };

        foreach ($this->data['called_listeners'] as $eventName => $calledListener) {
            foreach ($calledListener as $listner) {
                $called[] = [$listner['priority'], $listner['pretty']];
            }

            $calledContent .= $this->createTable($called, $tableConfig($eventName, 'No events have been recorded. Check that debugging is enabled in the kernel.'));
        }

        foreach ($this->data['not_called_listeners'] as $eventName => $calledListener) {
            foreach ($calledListener as $listner) {
                $notCalled[] = [$listner['priority'], $listner['pretty']];
            }

            $notCalledContent .= $this->createTable(
                $notCalled,
                $tableConfig(
                    $eventName,
                    '<p><strong>There are no uncalled listeners.</strong></p>'
                    . '<p>All listeners were called for this request or an error occurred when trying to collect uncalled listeners'
                    . '(in which case check the logs to get more information).</p>'
                )
            );
        }

        $orphanedEventsContent = $this->createTable(
            $this->data['orphaned_events'],
            [
                'headers' => ['events'],
                'vardumper' => false,
                'empty_text' => '<p><strong>There are no orphaned events.</strong></p>'
                    . '<p>All dispatched events were handled or an error occurred when trying to collect orphaned events'
                    . '(in which case check the logs to get more information).</p>',
            ]
        );

        return $this->createTabs([
            [
                'name' => 'Called Listeners <span class="counter">' . \count($called) . '</span>',
                'content' => $calledContent,
            ],
            [
                'name' => 'Not Called Listeners <span class="counter">' . \count($notCalled) . '</span>',
                'content' => $notCalledContent,
            ],
            [
                'name' => 'Orphaned events <span class="counter">' . \count($this->data['orphaned_events']) . '</span>',
                'content' => $orphanedEventsContent,
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->data = [];

        $this->eventManager->reset();
    }
}
