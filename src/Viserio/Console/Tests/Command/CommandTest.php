<?php
namespace Viserio\Console\Tests\Command;

use Mockery as Mock;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Viserio\Console\Application;
use Viserio\Console\Tests\Fixture\ViserioSecCommand as ViserioCommand;
use Viserio\Console\Tests\Mock\Container as MockContainer;
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

    public function tearDown()
    {
        Mock::close();
    }

    public function setUp()
    {
        $container = new MockContainer([
            'foo' => function (OutputInterface $output) {
                $output->write('hello');
            },
        ]);
        $events = Mock::mock('Symfony\Component\EventDispatcher\EventDispatcherInterface', ['addListener' => null]);

        $this->application = new Application($container, $events, '1.0.0');

        $this->invoker = (new Invoker())
            ->injectByTypeHint(true)
            ->injectByParameterName(true)
            ->setContainer($this->application->getContainer());
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
    //     $events = Mock::mock('Symfony\Component\EventDispatcher\EventDispatcherInterface', ['addListener' => null]);
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
