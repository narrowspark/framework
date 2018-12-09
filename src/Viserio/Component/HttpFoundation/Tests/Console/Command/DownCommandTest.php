<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFoundation\Tests\Console\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Contract\Console\Kernel as ConsoleKernelContract;
use Viserio\Component\HttpFoundation\Console\Command\DownCommand;
use Viserio\Component\Support\Invoker;

/**
 * @internal
 */
final class DownCommandTest extends MockeryTestCase
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

        $command = new DownCommand();
        $command->setInvoker(new Invoker());

        $this->command = $command;
    }

    public function testCommand(): void
    {
        $framework = \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'framework';
        $down      = $framework . \DIRECTORY_SEPARATOR . 'down';

        @\mkdir($framework);

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
        $tester->execute(['--message' => 'test', '--retry' => 1]);

        $output = $tester->getDisplay(true);

        $this->assertEquals("Application is now in maintenance mode.\n", $output);

        $data = \json_decode(\file_get_contents($down), true);

        $this->assertIsInt($data['time']);
        $this->assertSame('test', $data['message']);
        $this->assertSame(1, $data['retry']);

        @\unlink($down);
        @\rmdir($framework);
    }
}
