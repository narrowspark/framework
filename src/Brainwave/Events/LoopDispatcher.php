<?php

namespace Brainwave\Events;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.9.8-dev
 */

use Brainwave\Contracts\Events\Loops as LoopsContract;
use Interop\Container\ContainerInterface as ContainerContract;

/**
 * Dispatcher.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class LoopDispatcher implements LoopsContract
{
    /**
     * Async events
     *
     * @var array
     */
    protected $asyncEvents = [];

    /**
     * Container instance.
     *
     * @var \Interop\Container\ContainerInterface
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param ContainerContract $container
     */
    public function __construct(ContainerContract $container)
    {
        $this->container       = $container;
    }

    /**
     * Dispatch all saved events.
     *
     * @return void
     */
    public function dispatchAsync()
    {
        foreach ($this->asyncEvents as $eachEntry) {
            $this->dispatcher->dispatch($eachEntry['name'], $eachEntry['event']);
        }
    }

    /**
     * Store an asynchronous event to be dispatched later.
     *
     * @param string                                       $eventName
     * @param Symfony\Component\EventDispatcher\Event|null $event
     *
     * @return void
     */
    public function addAsyncEvent($eventName, Event $event = null)
    {
        $this->asyncEvents[] = [
            'name'  => $eventName,
            'event' => $event,
        ];
    }
}
