<?php
declare(strict_types=1);
namespace Viserio\Console\Tests\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Viserio\Console\Application;
use Viserio\Console\Tests\Fixture\ViserioCommand;
use Viserio\Console\Tests\Fixture\ViserioSecCommand;
use Viserio\Events\Dispatcher;
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

        $this->application = new Application($container, new Dispatcher($container), '1.0.0');

        $this->invoker = (new Invoker())
            ->injectByTypeHint(true)
            ->injectByParameterName(true)
            ->setContainer($this->application->getContainer());
    }

    public function testGetNormalVerbosity()
    {
        $command = new ViserioSecCommand();
        $this->assertSame(32, $command->getVerbosity());
    }

    public function testGetVerbosityLevelFromCommand()
    {
        $command = new ViserioSecCommand();
        $this->assertSame(128, $command->getVerbosity(128));

        $command = new ViserioSecCommand();
        $this->assertSame(128, $command->getVerbosity('vv'));
    }

    public function testSetVerbosityLevelToCommand()
    {
        $command = new ViserioSecCommand();
        $command->setVerbosity(256);
        $this->assertSame(256, $command->getVerbosity());
    }

    public function testGetOptionFromCommand()
    {
        $command = new ViserioSecCommand();
        $command->setApplication($this->application);
        $command->setInvoker($this->invoker);

        $command->run(new StringInput(''), new NullOutput());

        $this->assertSame(false, $command->option('yell'));
        $this->assertInternalType('array', $command->option());
    }

    public function testGetArgumentFromCommand()
    {
        $command = new ViserioSecCommand();
        $command->setApplication($this->application);
        $command->setInvoker($this->invoker);

        $command->run(new StringInput(''), new NullOutput());

        $this->assertSame(null, $command->argument('name'));
        $this->assertInternalType('array', $command->argument());
    }
}
