<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFoundation\Tests\Console\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Contract\Console\Kernel as ConsoleKernelContract;
use Viserio\Component\HttpFoundation\Console\Command\UpCommand;
use Viserio\Component\Support\Invoker;

/**
 * @internal
 */
final class UpCommandTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Console\Command\AbstractCommand
     */
    private $command;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $command = new UpCommand();
        $command->setInvoker(new Invoker());

        $this->command = $command;
    }

    public function testCommand(): void
    {
        $framework = \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'framework';
        $down      = $framework . \DIRECTORY_SEPARATOR . 'down';

        \mkdir($framework);
        \file_put_contents($down, 'test');

        $kernel = $this->mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('getStoragePath')
            ->once()
            ->with('framework' . \DIRECTORY_SEPARATOR . 'down')
            ->andReturn($down);

        $container = new ArrayContainer([
            ConsoleKernelContract::class => $kernel,
        ]);

        $this->command->setContainer($container);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        $this->assertEquals("Application is now live.\n", $output);

        \rmdir($framework);
    }
}
