<?php
namespace Viserio\Events\Interfaces;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface EventsAwareInterface
{
    /**
     * [setEventDispatcher description].
     *
     * @param EventDispatcherInterface $logger [description]
     */
    public function setEventDispatcher(EventDispatcherInterface $logger);
}
