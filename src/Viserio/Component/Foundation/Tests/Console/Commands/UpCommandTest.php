<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Console\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Contracts\Console\Kernel as ConsoleKernelContract;
use Viserio\Component\Foundation\Console\Command\UpCommand;

class UpCommandTest extends MockeryTestCase
{
    public function testCommand()
    {
        $framework = __DIR__ . '/../../Fixtures/framework';
        $down      = $framework . '/down';

        if (! is_dir($framework)) {
            mkdir($framework);
        }

        file_put_contents($down, 'test');

        $kernel = $this->mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('storagePath')
            ->once()
            ->with('framework/down')
            ->andReturn($down);

        $container = new ArrayContainer([
            ConsoleKernelContract::class => $kernel,
        ]);

        $command = new UpCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertEquals("Application is now live.\n", $output);

        if (is_dir($framework)) {
            rmdir($framework);
        }
    }
}
