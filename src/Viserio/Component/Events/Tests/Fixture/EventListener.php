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

namespace Viserio\Component\Events\Tests\Fixture;

class EventListener
{
    public $onAnyInvoked = 0;

    public $onCoreInvoked = 0;

    public $onCoreRequestInvoked = 0;

    public $onExceptionInvoked = 0;

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
