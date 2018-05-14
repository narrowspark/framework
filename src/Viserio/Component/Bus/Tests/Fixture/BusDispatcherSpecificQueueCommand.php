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

namespace Viserio\Component\Bus\Tests\Fixture;

use Viserio\Contract\Queue\ShouldQueue as ShouldQueueContract;

class BusDispatcherSpecificQueueCommand implements ShouldQueueContract
{
    public $queue = 'foo';
}
