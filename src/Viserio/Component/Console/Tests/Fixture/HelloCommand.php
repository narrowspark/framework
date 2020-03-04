<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Console\Tests\Fixture;

use Viserio\Component\Console\Command\AbstractCommand;

class HelloCommand extends AbstractCommand
{
    /** @var null|string The default command name */
    protected static $defaultName = 'hello';

    public function handle(LazyWhiner $lazyWhiner): int
    {
        $lazyWhiner->whine($this);

        $this->getOutput()->write('Hello World!');

        return 0;
    }
}
