<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;
use Viserio\Bridge\Twig\Command\DebugCommand;
use Viserio\Component\Contract\View\Finder as FinderContract;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\Support\Invoker;
use Viserio\Component\View\ViewFinder;

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
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $config;

    /**
     * @var \Viserio\Component\View\ViewFinder
     */
    private $finder;

    /**
     * @var \Twig\Loader\ArrayLoader
     */
    private $loader;

    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->config = [
            'viserio' => [
                'view' => [
                    'paths' => [
                        __DIR__ . '/../Fixture/',
                    ],
                ],
            ],
        ];

        $this->finder = new ViewFinder(new Filesystem(), $this->config);
        $this->loader = new ArrayLoader([]);
        $this->twig   = new Environment($this->loader);

        $this->container = new ArrayContainer(
            \array_merge(
                ['config' => $this->config],
                [
                    FinderContract::class  => $this->finder,
                    LoaderInterface::class => $this->loader,
                ]
            )
        );

        $command = new DebugCommand($this->twig);
        $command->setInvoker(new Invoker());

        $this->command = $command;
    }

    public function testDebug(): void
    {
        $this->command->setContainer($this->container);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute([], ['decorated' => false]);

        static::assertInternalType('string', $commandTester->getDisplay(true));
    }

    public function testDebugJsonFormat(): void
    {
        $this->command->setContainer($this->container);

        $commandTester = new CommandTester($this->command);
        $commandTester->execute(['--format' => 'json'], ['decorated' => false]);

        static::assertInternalType('string', $commandTester->getDisplay(true));
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

        $container = new ArrayContainer(
            \array_merge(
                ['config' => $this->config],
                [
                    FinderContract::class  => $this->finder,
                    LoaderInterface::class => $this->loader,
                ]
            )
        );

        $command = new DebugCommand(new Environment($filesystemLoader));
        $command->setContainer($container);
        $command->setInvoker(new Invoker());

        $commandTester = new CommandTester($command);
        $ret           = $commandTester->execute([], ['decorated' => false]);
        $loaderPaths   = '
Loader Paths
------------

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

        static::assertEquals(0, $ret, 'Returns 0 in case of success');
        static::assertContains($loaderPaths, \trim($commandTester->getDisplay(true)));
    }
}
