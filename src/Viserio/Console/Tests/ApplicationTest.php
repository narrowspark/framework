<?php
declare(strict_types=1);
namespace Viserio\Console\Tests;

use Mockery as Mock;
use Narrowspark\TestingHelper\ArrayContainer;
use StdClass;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Viserio\Console\Application;
use Viserio\Console\Tests\Fixture\SpyOutput;
use Viserio\Console\Tests\Fixture\ViserioCommand;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    private $application;

    public function setUp()
    {
        $stdClass = new StdClass();
        $stdClass->foo = 'hello';
        $stdClass2 = new StdClass();
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

    public function tearDown()
    {
        Mock::close();
    }

    public function testAllowsToDefineViserioCommand()
    {
        $viserioCommand = new ViserioCommand();
        $command = $this->application->add($viserioCommand);

        $this->assertSame($command, $this->application->get('demo:greet'));
    }

    public function testAllowsToDefineCommands()
    {
        $command = $this->application->command('foo', function () {
            return 1;
        });

        $this->assertSame($command, $this->application->get('foo'));
    }

    public function testAllowsToDefineDefaultValues()
    {
        $this->application->command('greet [firstname] [lastname]', function ($firstname, $lastname, Outputinterface $output) {
        });
        $this->application->defaults('greet', [
            'firstname' => 'John',
            'lastname' => 'Doe',
        ]);

        $definition = $this->application->get('greet')->getDefinition();

        $this->assertEquals('John', $definition->getArgument('firstname')->getDefault());
        $this->assertEquals('Doe', $definition->getArgument('lastname')->getDefault());
    }

    public function testItShouldRunSimpleCommand()
    {
        $this->application->command('greet', function (OutputInterface $output) {
            $output->write('hello');
        });

        $this->assertOutputIs('greet', 'hello');
    }

    public function testItShouldRunACommandWithAnArgument()
    {
        $this->application->command('greet name', function ($name, OutputInterface $output) {
            $output->write('hello ' . $name);
        });

        $this->assertOutputIs('greet john', 'hello john');
    }

    public function testItShouldRunACommandWithAnOptionalArgument()
    {
        $this->application->command('greet [name]', function ($name, OutputInterface $output) {
            $output->write('hello ' . $name);
        });

        $this->assertOutputIs('greet', 'hello ');
        $this->assertOutputIs('greet john', 'hello john');
    }

    public function testItShouldRunACommandWithAFlag()
    {
        $this->application->command('greet [-y|--yell]', function ($yell, OutputInterface $output) {
            $output->write(var_export($yell, true));
        });

        $this->assertOutputIs('greet', 'false');
        $this->assertOutputIs('greet -y', 'true');
        $this->assertOutputIs('greet --yell', 'true');
    }

    public function testItShouldRunACommandWithAnOption()
    {
        $this->application->command('greet [-i|--iterations=]', function ($iterations, OutputInterface $output) {
            $output->write($iterations === null ? 'null' : $iterations);
        });

        $this->assertOutputIs('greet', 'null');
        $this->assertOutputIs('greet -i 123', '123');
        $this->assertOutputIs('greet --iterations=123', '123');
    }

    public function testItShouldRunACommandWitMultipleOptions()
    {
        $this->application->command('greet [-d|--dir=]*', function ($dir, OutputInterface $output) {
            $output->write('[' . implode(', ', $dir) . ']');
        });

        $this->assertOutputIs('greet', '[]');
        $this->assertOutputIs('greet -d foo', '[foo]');
        $this->assertOutputIs('greet -d foo -d bar', '[foo, bar]');
        $this->assertOutputIs('greet --dir=foo --dir=bar', '[foo, bar]');
    }

    public function testItShouldInjectTypeHintInPriority()
    {
        $this->application->command('greet', function (OutputInterface $output, StdClass $param) {
            $output->write($param->foo);
        });

        $this->assertOutputIs('greet', 'hello');
    }

    public function testItCanResolveCallableStringFromContainer()
    {
        $this->application->command('greet', 'command.greet');

        $this->assertOutputIs('greet', 'hello');
    }

    public function testItCanResolveCallableArrayFromContainer()
    {
        $this->application->command('greet', 'command.arr.greet');

        $this->assertOutputIs('greet', 'hello');
    }

    public function testItcanInjectUsingTypeHints()
    {
        $this->application->command('greet', function (OutputInterface $output, StdClass $stdClass) {
            $output->write($stdClass->foo);
        });

        $this->assertOutputIs('greet', 'hello');
    }

    public function testItCanInjectUsingParameterNames()
    {
        $this->application->command('greet', function (OutputInterface $output, $stdClass) {
            $output->write($stdClass->foo);
        });

        $this->assertOutputIs('greet', 'hello');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Impossible to call the 'greet' command: Unable to invoke the callable because no value was given for parameter 1 ($fbo)
     */
    public function testItShouldThrowIfAParameterCannotBeResolved()
    {
        $this->application->command('greet', function ($fbo) {
        });

        $this->assertOutputIs('greet', '');
    }

    public function testRunsACommandViaItsAliasAndReturnsExitCode()
    {
        $this->application->command('foo', function () {
            return 1;
        }, ['bar']);

        $this->assertSame(1, $this->application->runCommand('bar'));
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
        $this->assertEquals($expected, $output->output);
    }
}
