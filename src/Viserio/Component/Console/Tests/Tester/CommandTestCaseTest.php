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

namespace Viserio\Component\Console\Tests\Tester;

use Viserio\Component\Console\Tester\CommandTestCase;
use Viserio\Component\Console\Tests\Fixture\OutputCommand;

/**
 * @internal
 *
 * @small
 */
final class CommandTestCaseTest extends CommandTestCase
{
    public function testExecuteCommand(): void
    {
        $output = $this->executeCommand(new OutputCommand());

        self::assertSame('Hello World!', $output->getDisplay(true));
    }
}
