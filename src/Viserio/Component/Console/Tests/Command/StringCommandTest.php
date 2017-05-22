<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Command;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Tests\Fixture\GreetCommand;

class StringCommandTest extends TestCase
{
    /**
     * @var \Viserio\Component\Console\Application
     */
    private $application;

    /**
     * @var \Viserio\Component\Console\StringCommand
     */
    private $command;

    public function setUp()
    {
        $this->application = new Application('1.0.0');
        $this->command     = $this->application->command('greet [name] [--yell] [--times=]', function () {
        });
    }

    public function testAllowsToDefineDescriptions()
    {
        $this->command->descriptions('Greet someone', [
            'name'    => 'Who?',
            '--yell'  => 'Yell?',
            '--times' => '# of times to greet?',
        ]);
        $definition = $this->command->getDefinition();

        self::assertEquals('Greet someone', $this->command->getDescription());
        self::assertEquals('Who?', $definition->getArgument('name')->getDescription());
        self::assertEquals('Yell?', $definition->getOption('yell')->getDescription());
        self::assertEquals('# of times to greet?', $definition->getOption('times')->getDescription());
    }

    public function testAllowsToDefineDefaultValues()
    {
        $this->command->defaults([
            'name'  => 'John',
            'times' => '1',
        ]);
        $definition = $this->command->getDefinition();

        self::assertEquals('John', $definition->getArgument('name')->getDefault());
        self::assertEquals('1', $definition->getOption('times')->getDefault());
    }

    public function testAllowsDefaultValuesToBeInferredFromClosureParameters()
    {
        $command = $this->application->command('greet [name] [--yell] [--times=]', function ($times = 15) {
        });
        $definition = $command->getDefinition();

        self::assertEquals(15, $definition->getOption('times')->getDefault());
    }

    public function testAllowsDefaultValuesToBeInferredFromCamelCaseParameters()
    {
        $command = $this->application->command('greet [name] [--yell] [--number-of-times=]', function ($numberOfTimes = 15) {
        });
        $definition = $command->getDefinition();

        self::assertEquals(15, $definition->getOption('number-of-times')->getDefault());
    }

    public function testAllowsDefaultValuesToBeInferredFromCallbleParameters()
    {
        $command    = $this->application->command('greet [name] [--yell] [--times=]', [new GreetCommand(), 'greet']);
        $definition = $command->getDefinition();

        self::assertEquals(15, $definition->getOption('times')->getDefault());
    }

    public function testSettingDefaultsFallsBackToOptionsWhenNoArgumentExists()
    {
        $this->command->defaults([
            'times' => '5',
        ]);
        $definition = $this->command->getDefinition();

        self::assertEquals(5, $definition->getOption('times')->getDefault());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSettingUnknownDefaultsThrowsAnException()
    {
        $this->command->defaults([
            'doesnotexist' => '0',
        ]);
    }

    public function testReflectingDefaultsForNonexistantInputsDoesNotThrowAnException()
    {
        $this->application->command('greet [name]', [new GreetCommand(), 'greet']);
        // An exception was thrown previously about the argument / option `times` not existing.

        self::assertTrue(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCommandWithAnInvalidStaticCallableShowThrowAnException()
    {
        $this->application->command('greet [name]', [GreetCommand::class, 'greet']);
    }
}
