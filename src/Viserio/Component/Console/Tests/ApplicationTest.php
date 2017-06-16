<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests;

use Error;
use Exception;
use LogicException;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use RuntimeException;
use stdClass;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Process\PhpExecutableFinder;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\ConsoleEvents;
use Viserio\Component\Console\Event\ConsoleCommandEvent;
use Viserio\Component\Console\Event\ConsoleErrorEvent;
use Viserio\Component\Console\Event\ConsoleTerminateEvent;
use Viserio\Component\Console\Tests\Fixture\SpyOutput;
use Viserio\Component\Console\Tests\Fixture\ViserioCommand;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Events\EventManager;

/**
 * Some code in this class it taken from silly.
 *
 * @author Matthieu Napoli https://github.com/mnapoli
 * @copyright Copyright (c) Matthieu Napoli
 */
class ApplicationTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Console\Application
     */
    private $application;

    public function setUp()
    {
        parent::setUp();

        $this->application = new Application('1.0.0');
    }

    public function testBootstrappers()
    {
        $_SERVER['ConsoleStarting'] = false;

        Application::starting(function () {
            $_SERVER['ConsoleStarting'] = 1;
        });

        new Application('1.0.0');

        self::assertSame(1, $_SERVER['ConsoleStarting']);

        Application::starting(function () {
            $_SERVER['ConsoleStarting'] = 2;
        });

        Application::clearBootstrappers();

        new Application('1.0.0');

        self::assertSame(1, $_SERVER['ConsoleStarting']);

        unset($_SERVER['ConsoleStarting']);
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
        $this->application->command('greet [-d|--dir=*]', function ($dir, OutputInterface $output) {
            $output->write('[' . implode(', ', $dir) . ']');
        });

        self::assertOutputIs('greet', '[]');
        self::assertOutputIs('greet -d foo', '[foo]');
        self::assertOutputIs('greet -d foo -d bar', '[foo, bar]');
        self::assertOutputIs('greet --dir=foo --dir=bar', '[foo, bar]');
    }

    public function testItShouldInjectTypeHintInPriority()
    {
        $stdClass       = new stdClass();
        $stdClass->foo  = 'hello';
        $stdClass2      = new stdClass();
        $stdClass2->foo = 'nope!';

        $container = new ArrayContainer([
            stdClass::class => $stdClass,
            'param'         => $stdClass2,
        ]);

        $this->application->setContainer($container);
        $this->application->command('greet', function (OutputInterface $output, stdClass $param) {
            $output->write($param->foo);
        });

        self::assertOutputIs('greet', 'hello');
    }

    public function testItCanResolveCallableStringFromContainer()
    {
        $container = new ArrayContainer([
            'command.greet' => function (OutputInterface $output) {
                $output->write('hello');
            },
        ]);

        $this->application->setContainer($container);
        $this->application->command('greet', 'command.greet');

        self::assertOutputIs('greet', 'hello');
    }

    public function testItCanResolveCallableArrayFromContainer()
    {
        $container = new ArrayContainer([
            'command.arr.greet' => [$this, 'foo'],
        ]);

        $this->application->setContainer($container);
        $this->application->command('greet', 'command.arr.greet');

        self::assertOutputIs('greet', 'hello');
    }

    public function testItCanInjectUsingTypeHints()
    {
        $stdClass       = new stdClass();
        $stdClass->foo  = 'hello';

        $container = new ArrayContainer([
            'stdClass' => $stdClass,
        ]);

        $this->application->setContainer($container);
        $this->application->command('greet', function (OutputInterface $output, stdClass $stdClass) {
            $output->write($stdClass->foo);
        });

        self::assertOutputIs('greet', 'hello');
    }

    public function testItCanInjectUsingParameterNames()
    {
        $stdClass       = new stdClass();
        $stdClass->foo  = 'hello';

        $container = new ArrayContainer([
            'stdClass' => $stdClass,
        ]);

        $this->application->setContainer($container);
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
     * @expectedException \Symfony\Component\Debug\Exception\FatalThrowableError
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

    public function testItCanRunASingleCommandApplication()
    {
        $this->application->command('run', function (OutputInterface $output) {
            $output->write('hello');
        });

        $this->application->setDefaultCommand('run');

        self::assertOutputIs('run', 'hello');
    }

    /**
     * @expectedException \Symfony\Component\Debug\Exception\FatalThrowableError
     * @expectedExceptionMessage Impossible to call the 'greet' command: 'foo' is not a callable
     */
    public function testItShouldThrowIfTheCommandIsNotACallable()
    {
        $this->application->command('greet', 'foo');

        self::assertOutputIs('greet', '');
    }

    public function testItCanRunAsASingleCommandApplication()
    {
        $this->application->command('run', function (OutputInterface $output) {
            $output->write('hello');
        });
        $this->application->setDefaultCommand('run');

        self::assertOutputIs('', 'hello');
    }

    public function testConsoleErrorEventIsTriggeredOnCommandNotFound()
    {
        $eventManager = new EventManager();
        $eventManager->attach(ConsoleEvents::ERROR, function (ConsoleErrorEvent $event) {
            self::assertNull($event->getCommand());
            self::assertInstanceOf(CommandNotFoundException::class, $event->getError());

            $event->getOutput()->write('silenced command not found');
            $event->markErrorAsHandled();
        });

        $this->application->setEventManager($eventManager);

        $tester = new ApplicationTester($this->application);
        $tester->run(['command' => 'unknown']);

        self::assertContains('silenced command not found', $tester->getDisplay());
        self::assertEquals(0, $tester->getStatusCode());
    }

    public function testRunWithDispatcher()
    {
        $application = $this->application;
        $application->setEventManager($this->getDispatcher());
        $application->register('foo')->setCode(function (InputInterface $input, OutputInterface $output) {
            $output->write('foo.');
        });

        $tester = new ApplicationTester($application);
        $tester->run(['command' => 'foo']);

        self::assertEquals('before.foo.after.' . PHP_EOL, $tester->getDisplay());
    }

    public function testRunDispatchesAllEventsWithError()
    {
        $application = $this->application;
        $application->setEventManager($this->getDispatcher());
        $application->setCatchExceptions(true);

        $application->register('dym')->setCode(function (InputInterface $input, OutputInterface $output) {
            $output->write('dym.');

            throw new Error('dymerr');
        });

        $tester = new ApplicationTester($application);
        $tester->run(['command' => 'dym']);

        self::assertContains('before.dym.error.after.', $tester->getDisplay(), 'The PHP Error did not dispached events');
    }

    public function testRunWithErrorCatchExceptionsFailingStatusCode()
    {
        $application = $this->application;
        $application->setEventManager($this->getDispatcher());
        $application->setCatchExceptions(true);

        $application->register('dym')->setCode(function (InputInterface $input, OutputInterface $output) {
            $output->write('dym.');

            throw new Error('dymerr');
        });

        $tester = new ApplicationTester($application);
        $tester->run(['command' => 'dym']);

        self::assertSame(1, $tester->getStatusCode(), 'Status code should be 1');
    }

    public function testRunWithErrorFailingStatusCode()
    {
        $application = $this->application;
        $application->setEventManager($this->getDispatcher());
        $application->setCatchExceptions(true);

        $application->register('dus')->setCode(function (InputInterface $input, OutputInterface $output) {
            $output->write('dus.');

            throw new Error('duserr');
        });

        $tester = new ApplicationTester($application);
        $tester->run(['command' => 'dus']);

        self::assertSame(1, $tester->getStatusCode(), 'Status code should be 1');
    }

    public function testRunWithDispatcherSkippingCommand()
    {
        $application = $this->application;
        $application->setEventManager($this->getDispatcher(true));
        $application->setCatchExceptions(true);

        $application->register('foo')->setCode(function (InputInterface $input, OutputInterface $output) {
            $output->write('foo.');
        });

        $tester   = new ApplicationTester($application);
        $exitCode = $tester->run(['command' => 'foo']);

        self::assertContains('before.after.', $tester->getDisplay());
        self::assertEquals(ConsoleCommandEvent::RETURN_CODE_DISABLED, $exitCode);
    }

    public function testRunWithDispatcherAccessingInputOptions()
    {
        $noInteractionValue = false;
        $quietValue         = true;
        $dispatcher         = $this->getDispatcher();
        $dispatcher->attach(ConsoleEvents::COMMAND, function (ConsoleCommandEvent $event) use (&$noInteractionValue, &$quietValue) {
            $input = $event->getInput();
            $noInteractionValue = $input->getOption('no-interaction');
            $quietValue = $input->getOption('quiet');
        });

        $application = $this->application;
        $application->setEventManager($dispatcher);
        $application->setCatchExceptions(true);

        $application->register('foo')->setCode(function (InputInterface $input, OutputInterface $output) {
            $output->write('foo.');
        });

        $tester = new ApplicationTester($application);
        $tester->run(['command' => 'foo', '--no-interaction' => true]);

        self::assertTrue($noInteractionValue);
        self::assertFalse($quietValue);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage error
     */
    public function testRunWithExceptionAndDispatcher()
    {
        $application = $this->application;
        $application->setEventManager($this->getDispatcher());

        $application->register('foo')->setCode(function (InputInterface $input, OutputInterface $output) {
            throw new RuntimeException('foo');
        });

        $tester = new ApplicationTester($application);
        $tester->run(['command' => 'foo']);
    }

    public function testRunDispatchesAllEventsWithException()
    {
        $application = $this->application;
        $application->setEventManager($this->getDispatcher());
        $application->setCatchExceptions(true);

        $application->register('foo')->setCode(function (InputInterface $input, OutputInterface $output) {
            $output->write('foo.');
            throw new RuntimeException('foo');
        });

        $tester = new ApplicationTester($application);
        $tester->run(['command' => 'foo']);

        self::assertContains('before.foo.error.after.', $tester->getDisplay());
    }

    public function testRunWithDispatcherAddingInputOptions()
    {
        $extraValue = null;
        $dispatcher = $this->getDispatcher();

        $dispatcher->attach(ConsoleEvents::COMMAND, function (ConsoleCommandEvent $event) use (&$extraValue) {
            $definition = $event->getCommand()->getDefinition();
            $input = $event->getInput();

            $definition->addOption(new InputOption('extra', null, InputOption::VALUE_REQUIRED));
            $input->bind($definition);

            $extraValue = $input->getOption('extra');
        });

        $application = $this->application;
        $application->setEventManager($dispatcher);
        $application->register('foo')->setCode(function (InputInterface $input, OutputInterface $output) {
            $output->write('foo.');
        });

        $tester = new ApplicationTester($application);
        $tester->run(['command' => 'foo', '--extra' => 'some test value']);

        self::assertEquals('some test value', $extraValue);
    }

    public function testRunDispatchesAllEventsWithExceptionInListener()
    {
        $dispatcher = $this->getDispatcher();
        $dispatcher->attach(ConsoleEvents::COMMAND, function () {
            throw new RuntimeException('foo');
        });

        $application = $this->application;
        $application->setEventManager($dispatcher);
        $application->setCatchExceptions(true);

        $application->register('foo')->setCode(function (InputInterface $input, OutputInterface $output) {
            $output->write('foo.');
        });

        $tester = new ApplicationTester($application);
        $tester->run(['command' => 'foo']);

        self::assertContains('before.error.after.', $tester->getDisplay());
    }

    public function testRunAllowsErrorListenersToSilenceTheException()
    {
        $dispatcher = $this->getDispatcher();
        $dispatcher->attach(ConsoleEvents::ERROR, function (ConsoleErrorEvent $event) {
            $event->getOutput()->write('silenced.');
            $event->markErrorAsHandled();
        });
        $dispatcher->attach(ConsoleEvents::COMMAND, function () {
            throw new RuntimeException('foo');
        });

        $application = $this->application;
        $application->setEventManager($dispatcher);
        $application->register('foo')->setCode(function (InputInterface $input, OutputInterface $output) {
            $output->write('foo.');
        });

        $tester = new ApplicationTester($application);
        $tester->run(['command' => 'foo']);

        self::assertContains('before.error.silenced.after.', $tester->getDisplay());
        self::assertEquals(0, $tester->getStatusCode());
    }

    public function testRunReturnsIntegerExitCode()
    {
        $exception = new Exception('', 4);

        $application = $this->getMockBuilder(Application::class)->setConstructorArgs(['1'])->setMethods(['doRun'])->getMock();
        $application->setCatchExceptions(true);
        $application->expects($this->once())
            ->method('doRun')
            ->will($this->throwException($exception));

        $exitCode = $application->run(new ArrayInput([]), new NullOutput());

        self::assertSame(4, $exitCode, '->run() returns integer exit code extracted from raised exception');
    }

    public function testCerebroBinary()
    {
        self::assertSame('cerebro', Application::cerebroBinary());
    }

    public function testPhpBinary()
    {
        $finder = (new PhpExecutableFinder())->find(false);
        $php    = escapeshellarg($finder === false ? '' : $finder);

        self::assertSame($php, Application::phpBinary());
    }

    public function testFormatCommandString()
    {
        $finder = (new PhpExecutableFinder())->find(false);
        $php    = escapeshellarg($finder === false ? '' : $finder);

        self::assertSame($php . ' cerebro' . ' command.greet', Application::formatCommandString('command.greet'));
    }

    public function testShouldInjectTheSymfonyStyleObject()
    {
        $this->application->command('greet', function (SymfonyStyle $io) {
            $io->write('hello');
        });

        self::assertOutputIs('greet', 'hello');
    }

    public function testItShouldInjectTheOutputAndInputByName()
    {
        $this->application->command('greet name', function ($output, $input) {
            $output->write('hello ' . $input->getArgument('name'));
        });
        self::assertOutputIs('greet john', 'hello john');
    }

    public function testShouldInjectTheOutputAndInputByNameEvenIfAServiceHasTheSameName()
    {
        $container = new ArrayContainer([
              'input'  => 'foo',
              'output' => 'bar',
          ]);

        $this->application->setContainer($container);
        $this->application->command('greet name', function ($output, $input) {
            $output->write('hello ' . $input->getArgument('name'));
        });

        self::assertOutputIs('greet john', 'hello john');
    }

    public function testShouldInjectTheOutputAndInputByTypeHintOnInterfaces()
    {
        $this->application->command('greet name', function (OutputInterface $out, InputInterface $in) {
            $out->write('hello ' . $in->getArgument('name'));
        });

        self::assertOutputIs('greet john', 'hello john');
    }

    public function testShouldInjectTheOutputAndInputByTypeHintOnClasses()
    {
        $this->application->command('greet name', function (Output $out, Input $in) {
            $out->write('hello ' . $in->getArgument('name'));
        });

        self::assertOutputIs('greet john', 'hello john');
    }

    public function testShouldInjectTheOutputAndInputByTypeHintEvenIfAServiceHasTheSameName()
    {
        $container = new ArrayContainer([
              'in'  => 'foo',
              'out' => 'bar',
          ]);

        $this->application->setContainer($container);
        $this->application->command('greet name', function (OutputInterface $out, InputInterface $in) {
            $out->write('hello ' . $in->getArgument('name'));
        });

        self::assertOutputIs('greet john', 'hello john');
    }

    public function testItShouldRunASubcommand()
    {
        $this->application->command('foo', function (OutputInterface $output) {
            $output->write('hello');
        });

        $this->application->command('bar', function (OutputInterface $output) {
            $this->call('foo', [], $output);

            $output->write(' world');
        });

        self::assertOutputIs('bar', 'hello world');
    }

    public function testOutput()
    {
        $this->application->command('foo', function (OutputInterface $output) {
            $output->write('hello');
        });

        self::assertSame('', $this->application->output());

        $this->application->call('foo');

        self::assertSame('hello', $this->application->output());
    }

    public function testAllowsDefaultValuesToBeInferredFromCamelCaseParameters()
    {
        $command = $this->application->command('greet [name] [--yell] [--number-of-times=]', function ($numberOfTimes = 15) {
        });

        $definition = $command->getDefinition();

        self::assertEquals(15, $definition->getOption('number-of-times')->getDefault());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ['Viserio\Component\Console\Tests\ApplicationTest', 'foo'] is not a callable because 'foo' is a static method. Either use [new Viserio\Component\Console\Tests\ApplicationTest(), 'foo'] or configure a dependency injection container that supports autowiring.
     */
    public function testItShouldThrowIfTheCommandIsAMethodCallToAStaticMethod()
    {
        $this->application->command('greet', [__CLASS__, 'foo']);
        self::assertOutputIs('greet', '');
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

    protected function getDispatcher($skipCommand = false)
    {
        $dispatcher = new EventManager();

        $dispatcher->attach(ConsoleEvents::COMMAND, function (ConsoleCommandEvent $event) use ($skipCommand) {
            $event->getOutput()->write('before.');

            if ($skipCommand) {
                $event->disableCommand();
            } else {
                $event->enableCommand();
            }
        });

        $dispatcher->attach(ConsoleEvents::TERMINATE, function (ConsoleTerminateEvent $event) use ($skipCommand) {
            $event->getOutput()->writeln('after.');

            if (! $skipCommand) {
                $event->setExitCode(113);
            }
        });

        $dispatcher->attach(ConsoleEvents::ERROR, function (ConsoleErrorEvent $event) {
            $event->getOutput()->write('error.');
            $event->setError(new LogicException('error.', $event->getExitCode(), $event->getError()));
        });

        return $dispatcher;
    }

    /**
     * @param string     $command
     * @param string|int $expected
     */
    private function assertOutputIs($command, $expected)
    {
        $output = new SpyOutput();

        $this->application->run(new StringInput($command), $output);

        self::assertEquals($expected, $output->output);
    }
}
