<?php
namespace Viserio\Queue;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Viserio\Contracts\Queue\Adapter as AdapterContract;
use Viserio\Contracts\Queue\Pushable as PushableContract;

abstract class Queue implements PushableContract
{
    /**
     * @param AdapterContract          $adapter     The queue adapter
     * @param EventDispatcherInterface $dispatcher  The event dispatcher
     * @param bool                     $failUnknown Whether to fail jobs whose status hasn't been implicitly set:
     *                                              - true: jobs with status unknown are assumed to have not been consumed (fail)
     *                                              - false: jobs with status unknown are assumed to have been consumed (delete)
     */
    public function __construct(AdapterContract $adapter, EventDispatcherInterface $dispatcher, $failUnknown = false)
    {
        $this->adapter     = $adapter;
        $this->dispatcher  = $dispatcher;
        $this->failUnknown = $failUnknown;
    }
}
