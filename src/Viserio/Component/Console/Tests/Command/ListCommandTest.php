<?php
declare(strict_types=1);
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
 */
final class ListCommandTest extends TestCase
{
    /**
     * @var \Viserio\Component\Console\Application
     */
    private $application;

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
    }

    public function testListCommand(): void
    {
        $this->assertInstanceOf(ListCommand::class, $this->application->get('list'));

        $commandTester = new CommandTester($command = $this->application->get('list'));
        $commandTester->execute(['command' => $command->getName()], ['decorated' => false]);

        $output = <<<'EOF'
Cerebro  1.0.0

USAGE: cerebro <command> [options] [arguments]

where <command> is one of:
                                                      
  demo              cerebro demo:greet                
                    cerebro demo:hallo                
                                                      
  thisIsALongName   cerebro thisIsALongName:hallo
EOF;

        $this->assertEquals(\trim($output), \trim($commandTester->getDisplay(true)));
    }

    public function testListCommandWithDescription(): void
    {
        $this->assertInstanceOf(ListCommand::class, $this->application->get('list'));

        $commandTester = new CommandTester($command = $this->application->get('list'));
        $commandTester->execute(['command' => $command->getName(), '--show-description' => true], ['decorated' => false]);

        $output = <<<'EOF'
Cerebro  1.0.0

USAGE: cerebro <command> [options] [arguments]

where <command> is one of:
                                                                   
  demo              cerebro demo:greet              Greet someone  
                    cerebro demo:hallo              Greet someone  
                                                                   
  thisIsALongName   cerebro thisIsALongName:hallo   Greet someone
EOF;

        $this->assertEquals(\trim($output), \trim($commandTester->getDisplay(true)));
    }

    public function testListCommandWithNamespace(): void
    {
        $this->assertInstanceOf(ListCommand::class, $this->application->get('list'));

        $commandTester = new CommandTester($command = $this->application->get('list'));
        $commandTester->execute(['command' => $command->getName(), 'namespace' => 'demo'], ['decorated' => false]);

        $output = <<<'EOF'
Cerebro  1.0.0

Available commands for the [demo] namespace

USAGE: cerebro <command> [options] [arguments]

where <command> is one of:
                                
  demo   cerebro demo:greet     
         cerebro demo:hallo
EOF;

        $this->assertEquals(\trim($output), \trim($commandTester->getDisplay(true)));
    }

    public function testListCommandWithNamespaceAndDescription(): void
    {
        $this->assertInstanceOf(ListCommand::class, $this->application->get('list'));

        $commandTester = new CommandTester($command = $this->application->get('list'));
        $commandTester->execute(['command' => $command->getName(), 'namespace' => 'demo', '--show-description' => true], ['decorated' => false]);

        $output = <<<'EOF'
Cerebro  1.0.0

Available commands for the [demo] namespace

USAGE: cerebro <command> [options] [arguments]

where <command> is one of:
                                             
  demo   cerebro demo:greet   Greet someone  
         cerebro demo:hallo   Greet someone
EOF;

        $this->assertEquals(\trim($output), \trim($commandTester->getDisplay(true)));
    }
}
