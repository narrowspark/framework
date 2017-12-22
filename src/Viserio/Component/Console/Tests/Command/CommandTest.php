<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Output\SpyOutput;
use Viserio\Component\Console\Tests\Fixture\ViserioSecCommand;
use Viserio\Component\Support\Invoker;

class CommandTest extends TestCase
{
    /**
     * @var \Viserio\Component\Console\Application
     */
    private $application;

    /**
     * @var \Viserio\Component\Support\Invoker
     */
    private $invoker;

    public function setUp(): void
    {
        $container = new ArrayContainer([
            'foo' => function (OutputInterface $output): void {
                $output->write('hello');
            },
        ]);

        $this->application = new Application('1.0.0');
        $this->application->setContainer($container);

        $this->invoker = (new Invoker())
            ->injectByTypeHint(true)
            ->injectByParameterName(true)
            ->setContainer($container);
    }

    public function testGetNormalVerbosity(): void
    {
        $command = new ViserioSecCommand();

        self::assertSame(32, $command->getVerbosity());
    }

    public function testGetVerbosityLevelFromCommand(): void
    {
        $command = new ViserioSecCommand();

        self::assertSame(128, $command->getVerbosity(128));

        $command = new ViserioSecCommand();

        self::assertSame(128, $command->getVerbosity('vv'));
    }

    public function testSetVerbosityLevelToCommand(): void
    {
        $command = new ViserioSecCommand();
        $command->setVerbosity(256);

        self::assertSame(256, $command->getVerbosity());
    }

    public function testGetOptionFromCommand(): void
    {
        $command = $this->arrangeCommand();

        $command->run(new StringInput(''), new NullOutput());

        self::assertSame(
            [
                'help'           => false,
                'quiet'          => false,
                'verbose'        => false,
                'version'        => false,
                'ansi'           => false,
                'no-ansi'        => false,
                'no-interaction' => false,
                'env'            => null,
                'yell'           => false,
            ],
            $command->option()
        );
        self::assertFalse($command->option('yell'));
        self::assertFalse($command->hasOption('help'));
        self::assertInternalType('array', $command->option());
    }

    public function testGetArgumentFromCommand(): void
    {
        $command = $this->arrangeCommand();

        $command->run(new StringInput(''), new NullOutput());

        self::assertNull($command->argument('name'));
        self::assertInternalType('array', $command->argument());
    }

    public function testTask(): void
    {
        $command = $this->arrangeCommand();
        $output  = new SpyOutput();

        $command->run(new StringInput(''), $output);
        $command->task('Downloading App', function () {
            return true;
        });

        self::assertEquals('Downloading App:✔
', $output->output);
    }

    /**
     * @return ViserioSecCommand
     */
    private function arrangeCommand(): ViserioSecCommand
    {
        $command = new ViserioSecCommand();
        $command->setApplication($this->application);
        $command->setInvoker($this->invoker);

        return $command;
    }
}
