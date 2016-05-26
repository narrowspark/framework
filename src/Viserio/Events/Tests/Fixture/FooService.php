<?php
namespace Viserio\Events\Tests\Fixture;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;

class FooService implements EventSubscriberInterface
{
    public $string = '';

    public function onFoo(Event $e)
    {
        $this->string .= 'foo';
    }

    public function onBar1(Event $e)
    {
        $this->string .= 'bar1';
    }

    public function onBar2(Event $e)
    {
        $this->string .= 'bar2';
    }

    public function onBuzz(Event $e)
    {
        $this->string .= 'buzz';
    }

    public static function getSubscribedEvents()
    {
        return [
            'foo' => 'onFoo',
            'bar' => [
                ['onBar1'],
                ['onBar2', 10],
            ],
            'buzz' => ['onBuzz', 5],
        ];
    }
}
