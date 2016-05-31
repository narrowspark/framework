<?php
namespace Viserio\Events\Tests\Fixture;

class TestEventListener
{
    public $onAnyInvoked = 0;
    public $onCoreInvoked = 0;
    public $onCoreRequestInvoked = 0;
    public $onExceptionInvoked = 0;

    public function onAny($event)
    {
        ++$this->onAnyInvoked;
    }

    public function onCore($event)
    {
        ++$this->onCoreInvoked;
    }

    public function onCoreRequest($event)
    {
        ++$this->onCoreRequestInvoked;
    }

    public function onException($event)
    {
        ++$this->onExceptionInvoked;
    }
}
