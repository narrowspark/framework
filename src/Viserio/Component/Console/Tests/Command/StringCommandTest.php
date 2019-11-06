<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Console\Tests\Command;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Tests\Fixture\GreetCommand;

/**
 * @internal
 *
 * @small
 */
final class StringCommandTest extends TestCase
{
    /** @var \Viserio\Component\Console\Application */
    private $application;

    /** @var \Viserio\Component\Console\Command\StringCommand */
    private $command;

    protected function setUp(): void
    {
        $this->application = new Application('1.0.0');
        $this->command = $this->application->command('greet [name] [--yell] [--times=]', function (): void {
        });
    }

    public function testAllowsToDefineDescriptions(): void
    {
        $this->command->descriptions('Greet someone', [
            'name' => 'Who?',
            '--yell' => 'Yell?',
            '--times' => '# of times to greet?',
        ]);
        $definition = $this->command->getDefinition();

        self::assertEquals('Greet someone', $this->command->getDescription());
        self::assertEquals('Who?', $definition->getArgument('name')->getDescription());
        self::assertEquals('Yell?', $definition->getOption('yell')->getDescription());
        self::assertEquals('# of times to greet?', $definition->getOption('times')->getDescription());
    }

    public function testAllowsToDefineDefaultValues(): void
    {
        $this->command->defaults([
            'name' => 'John',
            'times' => '1',
        ]);
        $definition = $this->command->getDefinition();

        self::assertEquals('John', $definition->getArgument('name')->getDefault());
        self::assertEquals('1', $definition->getOption('times')->getDefault());
    }

    public function testAllowsDefaultValuesToBeInferredFromClosureParameters(): void
    {
        $command = $this->application->command('greet [name] [--yell] [--times=]', function ($times = 15): void {
        });
        $definition = $command->getDefinition();

        self::assertEquals(15, $definition->getOption('times')->getDefault());
    }

    public function testAllowsDefaultValuesToBeInferredFromCamelCaseParameters(): void
    {
        $command = $this->application->command('greet [name] [--yell] [--number-of-times=]', function ($numberOfTimes = 15): void {
        });
        $definition = $command->getDefinition();

        self::assertEquals(15, $definition->getOption('number-of-times')->getDefault());
    }

    public function testAllowsDefaultValuesToBeInferredFromCallbleParameters(): void
    {
        $command = $this->application->command('greet [name] [--yell] [--times=]', [new GreetCommand(), 'greet']);
        $definition = $command->getDefinition();

        self::assertEquals(15, $definition->getOption('times')->getDefault());
    }

    public function testSettingDefaultsFallsBackToOptionsWhenNoArgumentExists(): void
    {
        $this->command->defaults([
            'times' => '5',
        ]);
        $definition = $this->command->getDefinition();

        self::assertEquals(5, $definition->getOption('times')->getDefault());
    }

    public function testSettingUnknownDefaultsThrowsAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->command->defaults([
            'doesnotexist' => '0',
        ]);
    }

    public function testReflectingDefaultsForNonExistantInputsDoesNotThrowAnException(): void
    {
        $this->application->command('greet [name]', [new GreetCommand(), 'greet']);
        // An exception was thrown previously about the argument / option `times` not existing.

        $this->expectNotToPerformAssertions();
    }

    public function testCommandWithAnInvalidStaticCallableShowThrowAnException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->application->command('greet [name]', [GreetCommand::class, 'greet']);
    }
}
