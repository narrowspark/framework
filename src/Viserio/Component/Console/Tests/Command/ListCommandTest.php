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

class ListCommandTest extends TestCase
{
    /**
     * @var \Viserio\Component\Console\Application
     */
    private $application;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->application = new Application('1.0.0', 'Cerebro');
        $this->application->add(new ViserioCommand());
        $this->application->add(new ViserioSecCommand());
        $this->application->add(new ViserioLongCommandName());
    }

    public function testListCommand()
    {
        self::assertInstanceOf(ListCommand::class, $this->application->get('list'));

        $commandTester = new CommandTester($command = $this->application->get('list'));
        $commandTester->execute(['command' => $command->getName()], ['decorated' => false]);

        $output = <<<'EOF'
Cerebro  1.0.0

USAGE: cerebro <command> [options] [arguments]

where <command> is one of:
                                                      
  help              cerebro help                      
                                                      
  list              cerebro list                      
                                                      
  demo              cerebro demo:hallo                
                    cerebro demo:greet                
                                                      
  thisIsALongName   cerebro thisIsALongName:hallo
EOF;

        self::assertEquals(trim($output), trim($commandTester->getDisplay(true)));
    }

    public function testListCommandWithDescription()
    {
        self::assertInstanceOf(ListCommand::class, $this->application->get('list'));

        $commandTester = new CommandTester($command = $this->application->get('list'));
        $commandTester->execute(['command' => $command->getName(), '--description' => true], ['decorated' => false]);

        $output = <<<'EOF'
Cerebro  1.0.0

USAGE: cerebro <command> [options] [arguments]

where <command> is one of:
                                                                                 
  help              cerebro help                    Displays help for a command  
                                                                                 
  list              cerebro list                    Lists console commands       
                                                                                 
  demo              cerebro demo:hallo              Greet someone                
                    cerebro demo:greet              Greet someone                
                                                                                 
  thisIsALongName   cerebro thisIsALongName:hallo   Greet someone
EOF;

        self::assertEquals(trim($output), trim($commandTester->getDisplay(true)));
    }

    public function testListCommandWithNamespace()
    {
        self::assertInstanceOf(ListCommand::class, $this->application->get('list'));

        $commandTester = new CommandTester($command = $this->application->get('list'));
        $commandTester->execute(['command' => $command->getName(), 'namespace' => 'demo'], ['decorated' => false]);

        $output = <<<'EOF'
Cerebro  1.0.0

USAGE: cerebro <command> [options] [arguments]

where <command> is one of:
                                                                        
  demo              cerebro demo:hallo                
                    cerebro demo:greet                
EOF;

        self::assertEquals(trim($output), trim($commandTester->getDisplay(true)));
    }
}
