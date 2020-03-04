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

class FooCommand extends AbstractCommand
{
    protected static $defaultName = 'foo:bar';

    protected $signature = 'foo:bar {id}';

    public function handle(): int
    {
        return 0;
    }
}
