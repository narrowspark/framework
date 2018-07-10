<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Tester;

use Viserio\Component\Console\Tester\CommandTestCase;
use Viserio\Component\Console\Tests\Fixture\OutputCommand;

/**
 * @internal
 */
final class CommandTestCaseTest extends CommandTestCase
{
    public function testExecuteCommand(): void
    {
        $output = $this->executeCommand(new OutputCommand());

        static::assertSame('Hello World!', $output->getDisplay(true));
    }
}
