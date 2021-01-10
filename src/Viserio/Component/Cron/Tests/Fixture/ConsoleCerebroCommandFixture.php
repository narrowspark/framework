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

namespace Viserio\Component\Cron\Tests\Fixture;

use Viserio\Component\Console\Command\AbstractCommand;

class ConsoleCerebroCommandFixture extends AbstractCommand
{
    protected $signature = 'foo:bar';

    protected $foo;

    public function __construct(DummyClassFixture $foo)
    {
        parent::__construct();

        $this->foo = $foo;
    }
}
