<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Tests\Fixture;

class EventListener
{
    public $onAnyInvoked         = 0;

    public $onCoreInvoked        = 0;

    public $onCoreRequestInvoked = 0;

    public $onExceptionInvoked   = 0;

    public function onAny($event = null): void
    {
        $this->onAnyInvoked++;
    }

    public function onCore($event = null): void
    {
        $this->onCoreInvoked++;
    }

    public function onCoreRequest($event = null): void
    {
        $this->onCoreRequestInvoked++;
    }

    public function onException($event = null): void
    {
        $this->onExceptionInvoked++;
    }
}
