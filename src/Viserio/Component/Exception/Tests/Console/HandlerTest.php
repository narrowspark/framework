<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Console;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Input\StringInput;
use Throwable;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Output\SpyOutput;
use Viserio\Component\Exception\Console\Handler;
use Viserio\Component\Exception\Console\SymfonyConsoleOutput;
use Viserio\Component\Exception\Tests\Fixtures\ErrorFixtureCommand;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class HandlerTest extends TestCase
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
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        if (! \extension_loaded('xdebug')) {
            $this->markTestSkipped('This test needs xdebug.');
        }

        parent::setUp();

        $this->rootDir           = self::normalizeDirectorySeparator(\dirname(__DIR__, 6));
        $this->pathVendorInvoker = self::normalizeDirectorySeparator($this->rootDir . '\vendor\php-di\invoker\src\Invoker.php');
        $this->pathInvoker       = self::normalizeDirectorySeparator($this->rootDir . '\src\Viserio\Component\Support\Invoker.php');
        $this->handler           = new Handler();
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

        self::assertSame("
RuntimeException : test

at $file : 69
65:         \$application = new Application();
66:         \$spyOutput   = new SpyOutput();
67: 
68:         \$application->command('greet', function (): void {
69:             throw new RuntimeException('test');
70:         });
71: 
72:         try {
73:             \$application->run(new StringInput('greet -v'), \$spyOutput);
74:         } catch (Throwable \$exception) {

Exception trace:

1   RuntimeException::__construct(\"test\")
    $file : 69

2   Viserio\Component\Console\Application::Viserio\Component\Exception\Tests\Console\{closure}()
    {$this->pathVendorInvoker} : 82

    {$this->pathVendorInvoker} : 82

4   Invoker\Invoker::call(Object(Closure))
    {$this->pathInvoker} : 89

5   Viserio\Component\Support\Invoker::call(Object(Closure))
    {$pathCommandResolver} : 97
", $spyOutput->output);
    }

    public function testRenderWithCommand(): void
    {
        $application = new Application();
        $output      = new SpyOutput();

        $application->add(new ErrorFixtureCommand());

        try {
            $application->run(new StringInput('error -v'), $output);
        } catch (Throwable $exception) {
            $this->handler->render(new SymfonyConsoleOutput($output), $exception);
        }

        $file        = self::normalizeDirectorySeparator(\dirname(__DIR__) . '\Fixtures\ErrorFixtureCommand.php');
        $commandPath = self::normalizeDirectorySeparator($this->rootDir . '\src\Viserio\Component\Console\Command\Command.php');

        self::assertSame("
Error : Class 'Viserio\Component\Exception\Tests\Fixtures\Console' not found

at $file : 16
12:     protected static \$defaultName = 'error';\n13: \n14:     public function handle(): void\n15:     {\n16:         Console::test('error');\n17:     }\n18: }
19: 

Exception trace:

1   Error::__construct(\"Class 'Viserio\Component\Exception\Tests\Fixtures\Console' not found\")
    $file : 16

2   Viserio\Component\Exception\Tests\Fixtures\ErrorFixtureCommand::handle()
    {$this->pathVendorInvoker} : 82

    {$this->pathVendorInvoker} : 82

4   Invoker\Invoker::call([])
    {$this->pathInvoker} : 89

5   Viserio\Component\Support\Invoker::call()
    {$commandPath} : 488
", $output->output);
    }

    public function testRenderWithCommandNoFound(): void
    {
        $application = new Application();
        $output      = new SpyOutput();

        try {
            $application->run(new StringInput('error -v'), $output);
        } catch (Throwable $exception) {
            $this->handler->render(new SymfonyConsoleOutput($output), $exception);
        }

        $viserioFile = self::normalizeDirectorySeparator($this->rootDir . '\src\Viserio\Component\Console\Application.php');
        $vendorFile  = self::normalizeDirectorySeparator($this->rootDir . '\vendor\symfony\console\Application.php');
        $handlerFile = self::normalizeDirectorySeparator($this->rootDir . '\src\Viserio\Component\Exception\Tests\Console\HandlerTest.php');

        self::assertSame("
Symfony\Component\Console\Exception\CommandNotFoundException : Command \"error\" is not defined.

at $vendorFile : 602
598:                 }
599:                 \$message .= implode(\"\\n    \", \$alternatives);
600:             }
601: 
602:             throw new CommandNotFoundException(\$message, \$alternatives);
603:         }
604: 
605:         // filter out aliases for commands which are already on the list
606:         if (count(\$commands) > 1) {
607:             \$commandList = \$this->commandLoader ? array_merge(array_flip(\$this->commandLoader->getNames()), \$this->commands) : \$this->commands;

Exception trace:

1   Symfony\Component\Console\Exception\CommandNotFoundException::__construct(\"Command \"error\" is not defined.\")
    $vendorFile : 602

2   Symfony\Component\Console\Application::find(\"error\")
    $vendorFile : 216

3   Symfony\Component\Console\Application::doRun(Object(Symfony\Component\Console\Input\StringInput), Object(Viserio\Component\Console\Output\SpyOutput))
    $viserioFile : 300

4   Viserio\Component\Console\Application::run(Object(Symfony\Component\Console\Input\StringInput), Object(Viserio\Component\Console\Output\SpyOutput))
    $handlerFile : 162

5   Viserio\Component\Exception\Tests\Console\HandlerTest::testRenderWithCommandNoFound()
    [internal] : 0
", $output->output);
    }
}
