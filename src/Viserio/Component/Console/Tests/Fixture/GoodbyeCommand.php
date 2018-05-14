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

class GoodbyeCommand extends AbstractCommand
{
    /** @var null|string The default command name */
    protected static $defaultName = 'goodbye';

    public function handle(LazyWhiner $lazyWhiner): int
    {
        $lazyWhiner->whine($this);

        $this->getOutput()->write('Goodbye World!');

        return 0;
    }
}
