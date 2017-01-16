<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Tests\Fixture;

class EventListener
{
    public $onAnyInvoked         = 0;
    public $onCoreInvoked        = 0;
    public $onCoreRequestInvoked = 0;
    public $onExceptionInvoked   = 0;

    public function onAny($event = null)
    {
        ++$this->onAnyInvoked;
    }

    public function onCore($event = null)
    {
        ++$this->onCoreInvoked;
    }

    public function onCoreRequest($event = null)
    {
        ++$this->onCoreRequestInvoked;
    }

    public function onException($event = null)
    {
        ++$this->onExceptionInvoked;
    }
}
