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
 * @version     0.10.0-dev
 */

use Brainwave\Contracts\Queue\Adapter as AdapterContract;
use Brainwave\Contracts\Queue\Pushable as PushableContract;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Queue.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10-dev
 */
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
