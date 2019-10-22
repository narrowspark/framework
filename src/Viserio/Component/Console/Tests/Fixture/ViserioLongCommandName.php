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

namespace Viserio\Component\Console\Tests\Fixture;

use Viserio\Component\Console\Command\AbstractCommand;

class ViserioLongCommandName extends AbstractCommand
{
    protected static $defaultName = 'thisIsALongName:hallo';

    protected $description = 'Greet someone';

    public function handle(): int
    {
        return 0;
    }
}
