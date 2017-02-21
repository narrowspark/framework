<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use stdClass;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Tests\Fixture\SpyOutput;
use Viserio\Component\Console\Tests\Fixture\ViserioCommand;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;

class ApplicationTest extends MockeryTestCase
{
    /**
     * @var Application
     */
    private $application;

    public function setUp()
    {
        parent::setUp();

        $stdClass       = new stdClass();
        $stdClass->foo  = 'hello';
        $stdClass2      = new stdClass();
        $stdClass2->foo = 'nope!';

        $container = new ArrayContainer([
            'command.greet' => function (OutputInterface $output) {
                $output->write('hello');
            },
            'stdClass'          => $stdClass,
            'param'             => 'bob',
            'stdClass2'         => $stdClass2,
            'command.arr.greet' => [$this, 'foo'],
        ]);

        $this->application = new Application($container, '1.0.0');
    }

    public function testCerebroStartingEventIstriggered()
    {
        $events = $this->mock(EventManagerContract::class);
        $events->shouldReceive('trigger')
            ->once();

        $container = new ArrayContainer([
            EventManagerContract::class => $events,
        ]);

        $this->application = new Application($container, '1.0.0');
    }

    public function testAllowsToDefineViserioCommand()
    {
        $command = $this->application->add(new ViserioCommand());

        self::assertSame($command, $this->application->get('demo:greet'));
    }

    public function testAllowsToDefineCommands()
    {
        $command = $this->application->command('foo', function () {
            return 1;
        });

        self::assertSame($command, $this->application->get('foo'));
    }

    public function testAllowsToDefineDefaultValues()
    {
        $this->application->command('greet [firstname] [lastname]', function ($firstname, $lastname, Outputinterface $output) {
        });
        $this->application->defaults('greet', [
            'firstname' => 'John',
            'lastname'  => 'Doe',
        ]);

        $definition = $this->application->get('greet')->getDefinition();

        self::assertEquals('John', $definition->getArgument('firstname')->getDefault());
        self::assertEquals('Doe', $definition->getArgument('lastname')->getDefault());
    }

    public function testItShouldRunSimpleCommand()
    {
        $this->application->command('greet', function (OutputInterface $output) {
            $output->write('hello');
        });

        self::assertOutputIs('greet', 'hello');
    }

    public function testItShouldRunSimpleCommandWithEvents()
    {
        $event = $this->mock(EventManagerContract::class);
        $event->shouldReceive('trigger')
            ->twice();

        $this->application->setEventManager($event);

        $this->application->command('greet', function (OutputInterface $output) {
            $output->write('hello');
        });

        self::assertOutputIs('greet', 'hello');
    }

    public function testItShouldRunACommandWithAnArgument()
    {
        $this->application->command('greet name', function ($name, OutputInterface $output) {
            $output->write('hello ' . $name);
        });

        self::assertOutputIs('greet john', 'hello john');
    }

    public function testItShouldRunACommandWithAnOptionalArgument()
    {
        $this->application->command('greet [name]', function ($name, OutputInterface $output) {
            $output->write('hello ' . $name);
        });

        self::assertOutputIs('greet', 'hello ');
        self::assertOutputIs('greet john', 'hello john');
    }

    public function testItShouldRunACommandWithAFlag()
    {
        $this->application->command('greet [-y|--yell]', function ($yell, OutputInterface $output) {
            $output->write(var_export($yell, true));
        });

        self::assertOutputIs('greet', 'false');
        self::assertOutputIs('greet -y', 'true');
        self::assertOutputIs('greet --yell', 'true');
    }

    public function testItShouldRunACommandWithAnOption()
    {
        $this->application->command('greet [-i|--iterations=]', function ($iterations, OutputInterface $output) {
            $output->write($iterations === null ? 'null' : $iterations);
        });

        self::assertOutputIs('greet', 'null');
        self::assertOutputIs('greet -i 123', '123');
        self::assertOutputIs('greet --iterations=123', '123');
    }

    public function testItShouldRunACommandWitMultipleOptions()
    {
        $this->application->command('greet [-d|--dir=]*', function ($dir, OutputInterface $output) {
            $output->write('[' . implode(', ', $dir) . ']');
        });

        self::assertOutputIs('greet', '[]');
        self::assertOutputIs('greet -d foo', '[foo]');
        self::assertOutputIs('greet -d foo -d bar', '[foo, bar]');
        self::assertOutputIs('greet --dir=foo --dir=bar', '[foo, bar]');
    }

    public function testItShouldInjectTypeHintInPriority()
    {
        $this->application->command('greet', function (OutputInterface $output, stdClass $param) {
            $output->write($param->foo);
        });

        self::assertOutputIs('greet', 'hello');
    }

    public function testItCanResolveCallableStringFromContainer()
    {
        $this->application->command('greet', 'command.greet');

        self::assertOutputIs('greet', 'hello');
    }

    public function testItCanResolveCallableArrayFromContainer()
    {
        $this->application->command('greet', 'command.arr.greet');

        self::assertOutputIs('greet', 'hello');
    }

    public function testItcanInjectUsingTypeHints()
    {
        $this->application->command('greet', function (OutputInterface $output, stdClass $stdClass) {
            $output->write($stdClass->foo);
        });

        self::assertOutputIs('greet', 'hello');
    }

    public function testItCanInjectUsingParameterNames()
    {
        $this->application->command('greet', function (OutputInterface $output, $stdClass) {
            $output->write($stdClass->foo);
        });

        self::assertOutputIs('greet', 'hello');
    }

    public function testItShouldMatchHyphenatedArgumentsToLowercaseParameters()
    {
        $this->application->command('greet first-name', function ($firstname, OutputInterface $output) {
            $output->write('hello ' . $firstname);
        });

        self::assertOutputIs('greet john', 'hello john');
    }

    public function testItShouldMatchHyphenatedArgumentsToMixedcaseParameters()
    {
        $this->application->command('greet first-name', function ($firstName, OutputInterface $output) {
            $output->write('hello ' . $firstName);
        });

        self::assertOutputIs('greet john', 'hello john');
    }

    public function testItShouldMatchHyphenatedOptionToLowercaseParameters()
    {
        $this->application->command('greet [--yell-louder]', function ($yelllouder, OutputInterface $output) {
            $output->write(var_export($yelllouder, true));
        });

        self::assertOutputIs('greet', 'false');
        self::assertOutputIs('greet --yell-louder', 'true');
    }

    public function testItShouldMatchHyphenatedOptionToMixedCaseParameters()
    {
        $this->application->command('greet [--yell-louder]', function ($yellLouder, OutputInterface $output) {
            $output->write(var_export($yellLouder, true));
        });

        self::assertOutputIs('greet', 'false');
        self::assertOutputIs('greet --yell-louder', 'true');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Impossible to call the 'greet' command: Unable to invoke the callable because no value was given for parameter 1 ($fbo)
     */
    public function testItShouldThrowIfAParameterCannotBeResolved()
    {
        $this->application->command('greet', function ($fbo) {
        });

        self::assertOutputIs('greet', '');
    }

    public function testRunsACommandViaItsAliasAndReturnsExitCode()
    {
        $this->application->command('foo', function ($output) {
            $output->write(1);
        }, ['bar']);

        self::assertOutputIs('bar', 1);
    }

    public function testitShouldRunACommandInTheScopeOfTheApplication()
    {
        $whatIsThis = null;

        $this->application->command('foo', function () use (&$whatIsThis) {
            $whatIsThis = $this;
        });

        self::assertOutputIs('foo', '');
        self::assertSame($this->application, $whatIsThis);
    }

    public function testItCanRunasASingleCommandApplication()
    {
        $this->application->command('run', function (OutputInterface $output) {
            $output->write('hello');
        });

        $this->application->setDefaultCommand('run');

        self::assertOutputIs('', 'hello');
    }

    /**
     * Fixture method.
     *
     * @param OutputInterface $output
     */
    public function foo(OutputInterface $output)
    {
        $output->write('hello');
    }

    /**
     * @param string $command
     * @param string $expected
     */
    private function assertOutputIs($command, $expected)
    {
        $output = new SpyOutput();

        $this->application->run(new StringInput($command), $output);

        self::assertEquals($expected, $output->output);
    }
}
