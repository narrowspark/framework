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

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Command\ListCommand;
use Viserio\Component\Console\Tests\Fixture\ViserioCommand;
use Viserio\Component\Console\Tests\Fixture\ViserioLongCommandName;
use Viserio\Component\Console\Tests\Fixture\ViserioSecCommand;

/**
 * @internal
 *
 * @small
 */
final class ListCommandTest extends TestCase
{
    /** @var \Viserio\Component\Console\Application */
    private $application;

    /** @var string */
    private $binCommand;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->application = new Application('1.0.0', 'Cerebro');
        $this->application->add(new ViserioCommand());
        $this->application->add(new ViserioSecCommand());
        $this->application->add(new ViserioLongCommandName());
        $this->binCommand = \defined('CEREBRO_BINARY') ? '\'cerebro\'' : 'cerebro';
    }

    public function testListCommand(): void
    {
        self::assertInstanceOf(ListCommand::class, $this->application->get('list'));

        $commandTester = new CommandTester($command = $this->application->get('list'));
        $commandTester->execute(['command' => $command->getName()], ['decorated' => false]);
        $space = \defined('CEREBRO_BINARY') ? '  ' : '';

        $output = <<<EOF
Cerebro 1.0.0

USAGE: cerebro <command> [options] [arguments]

where <command> is one of:
                                                      {$space}
  demo              cerebro demo:greet                
                    cerebro demo:hallo                
                                                      {$space}
  thisIsALongName   cerebro thisIsALongName:hallo
EOF;

        self::assertEquals(\str_replace('cerebro', $this->binCommand, \trim($output)), \trim($commandTester->getDisplay(true)));
    }

    public function testListCommandWithDescription(): void
    {
        self::assertInstanceOf(ListCommand::class, $this->application->get('list'));

        $commandTester = new CommandTester($command = $this->application->get('list'));
        $commandTester->execute(['command' => $command->getName(), '--show-description' => true], ['decorated' => false]);
        $space = \defined('CEREBRO_BINARY') ? '  ' : '';

        $output = <<<EOF
Cerebro 1.0.0

USAGE: cerebro <command> [options] [arguments]

where <command> is one of:
                                                                   {$space}
  demo              cerebro demo:greet              Greet someone  
                    cerebro demo:hallo              Greet someone  
                                                                   {$space}
  thisIsALongName   cerebro thisIsALongName:hallo   Greet someone
EOF;

        self::assertEquals(\str_replace('cerebro', $this->binCommand, \trim($output)), \trim($commandTester->getDisplay(true)));
    }

    public function testListCommandWithNamespace(): void
    {
        self::assertInstanceOf(ListCommand::class, $this->application->get('list'));

        $commandTester = new CommandTester($command = $this->application->get('list'));
        $commandTester->execute(['command' => $command->getName(), 'namespace' => 'demo'], ['decorated' => false]);
        $space = \defined('CEREBRO_BINARY') ? '  ' : '';

        $output = <<<EOF
Cerebro 1.0.0

Available commands for the [demo] namespace

USAGE: cerebro <command> [options] [arguments]

where <command> is one of:
                                {$space}
  demo   cerebro demo:greet     
         cerebro demo:hallo
EOF;

        self::assertEquals(\str_replace('cerebro', $this->binCommand, \trim($output)), \trim($commandTester->getDisplay(true)));
    }

    public function testListCommandWithNamespaceAndDescription(): void
    {
        self::assertInstanceOf(ListCommand::class, $this->application->get('list'));

        $commandTester = new CommandTester($command = $this->application->get('list'));
        $commandTester->execute(['command' => $command->getName(), 'namespace' => 'demo', '--show-description' => true], ['decorated' => false]);
        $space = \defined('CEREBRO_BINARY') ? '  ' : '';

        $output = <<<EOF
Cerebro 1.0.0

Available commands for the [demo] namespace

USAGE: cerebro <command> [options] [arguments]

where <command> is one of:
                                             {$space}
  demo   cerebro demo:greet   Greet someone  
         cerebro demo:hallo   Greet someone
EOF;

        self::assertEquals(\str_replace('cerebro', $this->binCommand, \trim($output)), \trim($commandTester->getDisplay(true)));
    }
}
