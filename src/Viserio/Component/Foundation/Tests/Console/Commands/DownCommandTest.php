<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Console\Commands;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Contracts\Console\Kernel as ConsoleKernelContract;
use Viserio\Component\Foundation\Console\Commands\DownCommand;

class DownCommandTest extends MockeryTestCase
{
    public function testCommand()
    {
        $framework = __DIR__ . '/../../Fixtures/framework';
        $down      = $framework . '/down';

        if (! is_dir($framework)) {
            mkdir($framework);
        }

        $kernel = $this->mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('storagePath')
            ->once()
            ->with('framework/down')
            ->andReturn($down);

        $container = new ArrayContainer([
            ConsoleKernelContract::class => $kernel,
        ]);

        $command = new DownCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute(['--message' => 'test', '--retry' => 1]);

        $output = $tester->getDisplay(true);

        self::assertEquals("Application is now in maintenance mode.\n", $output);

        $data = json_decode(file_get_contents($down), true);

        self::assertTrue(is_int($data['time']));
        self::assertSame('test', $data['message']);
        self::assertSame(1, $data['retry']);

        if (is_file($down)) {
            @unlink($down);
        }

        if (is_dir($framework)) {
            rmdir($framework);
        }
    }
}
