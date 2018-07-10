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
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

/**
 * @internal
 */
final class HandlerTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

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
            static::markTestSkipped('This test needs xdebug.');
        }

        parent::setUp();

        $this->rootDir           = self::normalizeDirectorySeparator(\dirname(__DIR__, 6));
        $this->pathVendorInvoker = self::normalizeDirectorySeparator($this->rootDir . '\vendor\php-di\invoker\src\Invoker.php');
        $this->pathInvoker       = self::normalizeDirectorySeparator($this->rootDir . '\src\Viserio\Component\Support\Invoker.php');

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
            throw new RuntimeException('test');
        });

        try {
            $application->run(new StringInput('greet -v'), $spyOutput);
        } catch (Throwable $exception) {
            $this->handler->render(new SymfonyConsoleOutput($spyOutput), $exception);
        }

        $file                = self::normalizeDirectorySeparator(__DIR__ . '\HandlerTest.php');
        $pathCommandResolver = self::normalizeDirectorySeparator($this->rootDir . '\src\Viserio\Component\Console\Command\CommandResolver.php');
        $file                = self::normalizeDirectorySeparator($file);

        $expected = "
RuntimeException : test

at ${file}:94
90:         \$application = new Application();
91:         \$spyOutput   = new SpyOutput();
92: 
93:         \$application->command('greet', function (): void {
94:             throw new RuntimeException('test');
95:         });
96: 
97:         try {
98:             \$application->run(new StringInput('greet -v'), \$spyOutput);
99:         } catch (Throwable \$exception) {

Exception trace:

1   RuntimeException::__construct(\"test\")
    ${file}:94

2   Viserio\\Component\\Console\\Application::Viserio\\Component\\Exception\\Tests\\Console\\{closure}()
    {$this->pathVendorInvoker}:82

    {$this->pathVendorInvoker}:82

4   Invoker\\Invoker::call(Object(Closure))
    {$this->pathInvoker}:89

5   Viserio\\Component\\Support\\Invoker::call(Object(Closure))
    {$pathCommandResolver}:97
";
        static::assertSame($expected, $spyOutput->output);
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

        $file        = self::normalizeDirectorySeparator(\dirname(__DIR__) . '\Fixture\ErrorFixtureCommand.php');
        $commandPath = self::normalizeDirectorySeparator($this->rootDir . '\src\Viserio\Component\Console\Command\AbstractCommand.php');

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
        static::assertSame($expected, $spyOutput->output);
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

        $viserioFile = self::normalizeDirectorySeparator($this->rootDir . '\src\Viserio\Component\Console\Application.php');
        $vendorFile  = self::normalizeDirectorySeparator($this->rootDir . '\vendor\symfony\console\Application.php');
        $handlerFile = self::normalizeDirectorySeparator($this->rootDir . '\src\Viserio\Component\Exception\Tests\Console\HandlerTest.php');

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
        static::assertContains($expected, $spyOutput->output);
    }
}
