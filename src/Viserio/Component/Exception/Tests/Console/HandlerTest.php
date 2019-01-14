<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Console;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Console\Input\StringInput;
use Throwable;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Output\SpyOutput;
use Viserio\Component\Exception\Console\Handler;
use Viserio\Component\Exception\Console\SymfonyConsoleOutput;
use Viserio\Component\Exception\Tests\Fixture\ErrorFixtureCommand;

/**
 * @internal
 */
final class HandlerTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Exception\Console\Handler
     */
    private $handler;

    /**
     * Vendor dir path.
     *
     * @var string
     */
    private $rootDir;

    /**
     * Vendor invoker path.
     *
     * @var string
     */
    private $pathVendorInvoker;

    /**
     * Invoker path.
     *
     * @var string
     */
    private $pathInvoker;

    /**
     * @var array
     */
    private $config;

    /**
     * @var \Mockery\MockInterface|\Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        if (! \extension_loaded('xdebug')) {
            $this->markTestSkipped('This test needs xdebug.');
        }

        parent::setUp();

        $this->rootDir           = \dirname(__DIR__, 6);
        $this->pathVendorInvoker = $this->rootDir . \DIRECTORY_SEPARATOR . 'vendor' . \DIRECTORY_SEPARATOR . 'php-di' . \DIRECTORY_SEPARATOR . 'invoker' . \DIRECTORY_SEPARATOR . 'src' . \DIRECTORY_SEPARATOR . 'Invoker.php';
        $this->pathInvoker       = $this->rootDir . \DIRECTORY_SEPARATOR . 'src' . \DIRECTORY_SEPARATOR . 'Viserio' . \DIRECTORY_SEPARATOR . 'Component' . \DIRECTORY_SEPARATOR . 'Support' . \DIRECTORY_SEPARATOR . 'Invoker.php';

        $this->config = [
            'viserio' => [
                'exception' => [
                    'env'   => 'dev',
                    'debug' => false,
                ],
            ],
        ];

        $this->logger    = $this->mock(LoggerInterface::class);
        $this->handler   = new Handler($this->config, $this->logger);
    }

    public function testRenderWithStringCommand(): void
    {
        $application = new Application();
        $spyOutput   = new SpyOutput();

        $application->command('greet', function (): void {
            throw new RuntimeException('test.');
        });

        try {
            $application->run(new StringInput('greet -v'), $spyOutput);
        } catch (Throwable $exception) {
            $this->handler->render(new SymfonyConsoleOutput($spyOutput), $exception);
        }

        $file                = __DIR__ . \DIRECTORY_SEPARATOR . 'HandlerTest.php';
        $pathCommandResolver = $this->rootDir . \DIRECTORY_SEPARATOR . 'src' . \DIRECTORY_SEPARATOR . 'Viserio' . \DIRECTORY_SEPARATOR . 'Component' . \DIRECTORY_SEPARATOR . 'Console' . \DIRECTORY_SEPARATOR . 'Command' . \DIRECTORY_SEPARATOR . 'CommandResolver.php';

        $expected = "
RuntimeException : test.

at ${file}:91
87:         \$application = new Application();
88:         \$spyOutput   = new SpyOutput();
89: 
90:         \$application->command('greet', function (): void {
91:             throw new RuntimeException('test.');
92:         });
93: 
94:         try {
95:             \$application->run(new StringInput('greet -v'), \$spyOutput);
96:         } catch (Throwable \$exception) {

Exception trace:

1   RuntimeException::__construct(\"test.\")
    ${file}:91

2   Viserio\\Component\\Console\\Application::Viserio\\Component\\Exception\\Tests\\Console\\{closure}()
    {$this->pathVendorInvoker}:82

    {$this->pathVendorInvoker}:82

4   Invoker\\Invoker::call(Object(Closure))
    {$this->pathInvoker}:89

5   Viserio\\Component\\Support\\Invoker::call(Object(Closure))
    {$pathCommandResolver}:97
";
        $this->assertSame($expected, $spyOutput->output);
    }

    public function testRenderWithCommand(): void
    {
        $application    = new Application();
        $spyOutput      = new SpyOutput();

        $application->add(new ErrorFixtureCommand());

        try {
            $application->run(new StringInput('error -v'), $spyOutput);
        } catch (Throwable $exception) {
            $this->handler->render(new SymfonyConsoleOutput($spyOutput), $exception);
        }

        $file        = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'ErrorFixtureCommand.php';
        $commandPath = $this->rootDir . \DIRECTORY_SEPARATOR . 'src' . \DIRECTORY_SEPARATOR . 'Viserio' . \DIRECTORY_SEPARATOR . 'Component' . \DIRECTORY_SEPARATOR . 'Console' . \DIRECTORY_SEPARATOR . 'Command' . \DIRECTORY_SEPARATOR . 'AbstractCommand.php';

        $expected = "
Error : Class 'Viserio\\Component\\Exception\\Tests\\Fixture\\Console' not found

at ${file}:16
12:     protected static \$defaultName = 'error';\n13: \n14:     public function handle(): int\n15:     {\n16:         Console::test('error');\n17: \n18:         return 1;\n19:     }\n20: }
21: 

Exception trace:

1   Error::__construct(\"Class 'Viserio\\Component\\Exception\\Tests\\Fixture\\Console' not found\")
    ${file}:16

2   Viserio\\Component\\Exception\\Tests\\Fixture\\ErrorFixtureCommand::handle()
    {$this->pathVendorInvoker}:82

    {$this->pathVendorInvoker}:82

4   Invoker\\Invoker::call([])
    {$this->pathInvoker}:89

5   Viserio\\Component\\Support\\Invoker::call()
    {$commandPath}:541
";
        $this->assertSame($expected, $spyOutput->output);
    }

    public function testRenderWithCommandNoFound(): void
    {
        $application = new Application();
        $spyOutput   = new SpyOutput();

        try {
            $application->run(new StringInput('error -v'), $spyOutput);
        } catch (Throwable $exception) {
            $this->handler->render(new SymfonyConsoleOutput($spyOutput), $exception);
        }

        $viserioFile = $this->rootDir . \DIRECTORY_SEPARATOR . 'src' . \DIRECTORY_SEPARATOR . 'Viserio' . \DIRECTORY_SEPARATOR . 'Component' . \DIRECTORY_SEPARATOR . 'Console' . \DIRECTORY_SEPARATOR . 'Application.php';
        $vendorFile  = $this->rootDir . \DIRECTORY_SEPARATOR . 'vendor' . \DIRECTORY_SEPARATOR . 'symfony' . \DIRECTORY_SEPARATOR . 'console' . \DIRECTORY_SEPARATOR . 'Application.php';
        $handlerFile = $this->rootDir . \DIRECTORY_SEPARATOR . 'src' . \DIRECTORY_SEPARATOR . 'Viserio' . \DIRECTORY_SEPARATOR . 'Component' . \DIRECTORY_SEPARATOR . 'Exception' . \DIRECTORY_SEPARATOR . 'Tests' . \DIRECTORY_SEPARATOR . 'Console' . \DIRECTORY_SEPARATOR . 'HandlerTest.php';

        $expected = <<<PHP

Symfony\\Component\\Console\\Exception\\CommandNotFoundException : Command "error" is not defined.

at ${vendorFile}:632
628:                 }
629:                 \$message .= implode("\\n    ", \$alternatives);
630:             }
631: 
632:             throw new CommandNotFoundException(\$message, \$alternatives);
633:         }
634: 
635:         // filter out aliases for commands which are already on the list
636:         if (\\count(\$commands) > 1) {
637:             \$commandList = \$this->commandLoader ? array_merge(array_flip(\$this->commandLoader->getNames()), \$this->commands) : \$this->commands;

Exception trace:

1   Symfony\\Component\\Console\\Exception\\CommandNotFoundException::__construct("Command "error" is not defined.")
    ${vendorFile}:632

2   Symfony\\Component\\Console\\Application::find("error")
    ${vendorFile}:226

3   Symfony\\Component\\Console\\Application::doRun(Object(Symfony\\Component\\Console\\Input\\StringInput), Object(Viserio\\Component\\Console\\Output\\SpyOutput))
    ${viserioFile}:335

4   Viserio\\Component\\Console\\Application::run(Object(Symfony\\Component\\Console\\Input\\StringInput), Object(Viserio\\Component\\Console\\Output\\SpyOutput))
    ${handlerFile}:189

5   Viserio\\Component\\Exception\\Tests\\Console\\HandlerTest::testRenderWithCommandNoFound()

PHP;
        $this->assertContains(
            \preg_replace('/\d+/u', '', $expected),
            \preg_replace('/\d+/u', '', $spyOutput->output)
        );
    }
}
