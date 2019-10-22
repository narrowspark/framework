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
