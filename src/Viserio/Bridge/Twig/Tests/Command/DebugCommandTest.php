<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Command;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\FilesystemLoader;
use Viserio\Bridge\Twig\Command\DebugCommand;
use Viserio\Component\Support\Invoker;

/**
 * @internal
 */
final class DebugCommandTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Bridge\Twig\Command\DebugCommand
     */
    private $command;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $command = new DebugCommand(new Environment(new ArrayLoader([])));
        $command->setInvoker(new Invoker());

        $this->command = $command;
    }

    public function testDebug(): void
    {
        $commandTester = new CommandTester($this->command);
        $ret           = $commandTester->execute([], ['decorated' => false]);

        $this->assertEquals(0, $ret, 'Returns 0 in case of success');
        $this->assertInternalType('string', $commandTester->getDisplay(true));
    }

    public function testDebugJsonFormat(): void
    {
        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['--format' => 'json'], ['decorated' => false]);

        $this->assertInternalType('string', $commandTester->getDisplay(true));
    }

    public function testLineSeparatorInLoaderPaths(): void
    {
        $filesystemLoader = new FilesystemLoader([], \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture');

        // these paths aren't realistic, they're configured to force the line separator
        $paths = [
            'Acme'                           => ['Extractor', 'Extractor'],
            '!Acme'                          => ['Extractor', 'Extractor'],
            FilesystemLoader::MAIN_NAMESPACE => ['Extractor', 'Extractor'],
        ];

        foreach ($paths as $namespace => $relDirs) {
            foreach ($relDirs as $relDir) {
                $filesystemLoader->addPath($relDir, $namespace);
            }
        }

        $command = new DebugCommand(new Environment($filesystemLoader));
        $command->setInvoker(new Invoker());

        $commandTester = new CommandTester($command);
        $ret           = $commandTester->execute([], ['decorated' => false]);
        $loaderPaths   = '
Configured Paths
----------------

 ----------- ------------- 
  Namespace   Paths        
 ----------- ------------- 
  @Acme       - Extractor  
              - Extractor  
                           
  @!Acme      - Extractor  
              - Extractor  
                           
  (None)      - Extractor  
              - Extractor  
 ----------- -------------';

        $this->assertEquals(0, $ret, 'Returns 0 in case of success');
        $this->assertContains($loaderPaths, \trim($commandTester->getDisplay(true)));
    }
}
