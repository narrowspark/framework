<?php
namespace Viserio\Events\Interfaces;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * EventsAwareInterface.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
interface EventsAwareInterface
{
    /**
     * [setEventDispatcher description].
     *
     * @param EventDispatcherInterface $logger [description]
     */
    public function setEventDispatcher(EventDispatcherInterface $logger);
}
