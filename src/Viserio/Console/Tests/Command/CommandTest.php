<?php

declare(strict_types=1);
namespace Viserio\Console\Tests\Command;

use Mockery as Mock;
use Narrowspark\TestingHelper\ArrayContainer;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Viserio\Console\Application;
use Viserio\Console\Tests\Fixture\ViserioSecCommand as ViserioCommand;
use Viserio\Support\Invoker;

class CommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var Invoker
     */
    private $invoker;

    public function setUp()
    {
        $container = new ArrayContainer([
            'foo' => function (OutputInterface $output) {
                $output->write('hello');
            },
        ]);

        $this->application = new Application($container, '1.0.0');

        $this->invoker = (new Invoker())
            ->injectByTypeHint(true)
            ->injectByParameterName(true)
            ->setContainer($this->application->getContainer());
    }

    public function tearDown()
    {
        Mock::close();
    }

    public function testGetNormalVerbosity()
    {
        $command = new ViserioCommand();
        $this->assertSame(32, $command->getVerbosity());
    }

    public function testGetVerbosityLevelFromCommand()
    {
        $command = new ViserioCommand();
        $this->assertSame(128, $command->getVerbosity(128));

        $command = new ViserioCommand();
        $this->assertSame(128, $command->getVerbosity('vv'));
    }

    public function testSetVerbosityLevelToCommand()
    {
        $command = new ViserioCommand();
        $command->setVerbosity(256);
        $this->assertSame(256, $command->getVerbosity());
    }

    // @TODO finish test.
    // public function testCallAnotherConsoleCommand()
    // {
    //     $container = new MockContainer();
    //     $events = Mock::mock('Viserio\Contracts\Events\Dispatcher', ['addListener' => null]);
    //
    //     $application = new Application($container, $events, '1.0.0');
    //     $application->command('foo', function (OutputInterface $output) {
    //         $output->write('hello');
    //     });
    //
    //     $command = new ViserioCommand();
    //     $command->setApplication($application);
    //     $command->setInvoker(
    //     (new Invoker())
    //         ->injectByTypeHint(true)
    //         ->injectByParameterName(true)
    //         ->setContainer($application->getContainer())
    //     );
    //     $command->run(new StringInput(''), new NullOutput());
    //
    //     $tester = new CommandTester($command);
    //
    //     $this->assertSame($application->get('foo'), $command->call('foo'));
    // }

    public function testGetOptionFromCommand()
    {
        $command = new ViserioCommand();
        $command->setApplication($this->application);
        $command->setInvoker($this->invoker);

        $command->run(new StringInput(''), new NullOutput());

        $this->assertSame(false, $command->option('yell'));
        $this->assertInternalType('array', $command->option());
    }

    public function testGetArgumentFromCommand()
    {
        $command = new ViserioCommand();
        $command->setApplication($this->application);
        $command->setInvoker($this->invoker);

        $command->run(new StringInput(''), new NullOutput());

        $this->assertSame(null, $command->argument('name'));
        $this->assertInternalType('array', $command->argument());
    }
}
