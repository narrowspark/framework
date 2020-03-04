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

namespace Viserio\Component\Console\Tests\Tester;

use Viserio\Component\Console\Tester\CommandTestCase;
use Viserio\Component\Console\Tests\Fixture\OutputCommand;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class CommandTestCaseTest extends CommandTestCase
{
    public function testExecuteCommand(): void
    {
        $output = $this->executeCommand(new OutputCommand());

        self::assertSame('Hello World!', $output->getDisplay(true));
    }
}
