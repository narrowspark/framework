<?php
namespace Viserio\Console\Test;

use Mockery as Mock;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\Output as SymfonyOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Viserio\Console\Application;

class ApplicationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    private $application;

    public function tearDown()
    {
        Mock::close();
    }

    public function setUp()
    {
        $app = new \Viserio\Container\Container();
        $events = Mock::mock('Symfony\Component\EventDispatcher\EventDispatcherInterface', ['addListener' => null]);

        $this->application = new \Viserio\Console\Application($app, $events);
    }

    /**
     * @test
     */
    public function allowsToDefineCommandDescriptions()
    {
        $this->application->command('greet name --yell', function () {});
        $this->application->descriptions('greet', 'Greet someone', [
            'name' => 'Who?',
            '--yell' => 'Yell?',
        ]);

        $command = $this->application->get('greet');

        $this->assertEquals('Greet someone', $command->getDescription());
        $this->assertEquals('Who?', $command->getDefinition()->getArgument('name')->getDescription());
        $this->assertEquals('Yell?', $command->getDefinition()->getOption('yell')->getDescription());
    }

    /**
     * @test
     */
    public function allowsToDefineDefaultValues()
    {
        $this->application->command('greet firstname? lastname?', function () {});
        $this->application->defaults('greet', [
            'firstname' => 'John',
            'lastname' => 'Doe',
        ]);

        $definition = $this->application->get('greet')->getDefinition();

        $this->assertEquals('John', $definition->getArgument('firstname')->getDefault());
        $this->assertEquals('Doe', $definition->getArgument('lastname')->getDefault());
    }

    /**
     * @test
     */
    public function itShouldRunASimpleCommand()
    {
        $this->application->command('greet', function (InputInterface $input, OutputInterface $output) {
            $output->write('hello');
        });

        $this->assertOutputIs('greet', 'hello');
    }

    /**
     * @test
     */
    public function itShouldRunACommandWithAnArgument()
    {
        $this->application->command('greet name', function (InputInterface $name, OutputInterface $output) {
            $output->write('hello '.$name);
        });

        $this->assertOutputIs('greet john', 'hello john');
    }

    /**
     * @test
     */
    public function itShouldRunACommandWithAnOptionalArgument()
    {
        $this->application->command('greet name?', function (InputInterface $name, OutputInterface $output) {
            $output->write('hello '.$name);
        });

        $this->assertOutputIs('greet', 'hello ');
        $this->assertOutputIs('greet john', 'hello john');
    }

    /**
     * @test
     */
    public function itShouldRunACommandWithAFlag()
    {
        $this->application->command('greet -y|--yell', function (InputInterface $yell, OutputInterface $output) {
            $output->write(var_export($yell, true));
        });

        $this->assertOutputIs('greet', 'false');
        $this->assertOutputIs('greet -y', 'true');
        $this->assertOutputIs('greet --yell', 'true');
    }

    /**
     * @test
     */
    public function itShouldRunACommandWithAnOption()
    {
        $this->application->command('greet -i|--iterations=', function (InputInterface $iterations, OutputInterface $output) {
            $output->write($iterations === null ? 'null' : $iterations);
        });

        $this->assertOutputIs('greet', 'null');
        $this->assertOutputIs('greet -i 123', '123');
        $this->assertOutputIs('greet --iterations=123', '123');
    }

    /**
     * @test
     */
    public function itShouldRunACommandWitMultipleOptions()
    {
        $this->application->command('greet -d|--dir=*', function (InputInterface $dir, OutputInterface $output) {
            $output->write('['.implode(', ', $dir).']');
        });

        $this->assertOutputIs('greet', '[]');
        $this->assertOutputIs('greet -d foo', '[foo]');
        $this->assertOutputIs('greet -d foo -d bar', '[foo, bar]');
        $this->assertOutputIs('greet --dir=foo --dir=bar', '[foo, bar]');
    }

    private function assertOutputIs($command, $expected)
    {
        $output = new SpyOutput();

        $this->application->run(new StringInput($command), $output);
        $this->assertEquals($expected, $output->output);
    }
}

class SpyOutput extends SymfonyOutput implements OutputInterface
{
    public $output;

    protected function doWrite($message, $newline)
    {
        $this->output .= $message.($newline ? "\n" : '');
    }
}
