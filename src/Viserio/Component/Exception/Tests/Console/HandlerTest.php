<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Console;

use PHPUnit\Framework\TestCase;
use ReflectionObject;
use RuntimeException;
use Symfony\Component\Console\Input\StringInput;
use Throwable;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Output\SpyOutput;
use Viserio\Component\Exception\Console\Handler;
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
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        if (\extension_loaded('xdebug')) {
            $this->markTestSkipped('@todo fix output with xdebug');
        }

        parent::setUp();

        $this->getVendorPath();
        $this->handler = new Handler();
    }

    public function testRenderWithStringCommand(): void
    {
        $application = new Application();
        $output      = new SpyOutput();

        $application->command('greet', function ($output): void {
            throw new RuntimeException('test');
        });

        try {
            $application->run(new StringInput('greet -v'), $output);
        } catch (Throwable $exception) {
            $this->handler->render($output, $exception);
        }

        $file = __DIR__ . '\HandlerTest.php';
        $path = $this->rootDir;

        if (\mb_strtolower(\mb_substr(PHP_OS, 0, 3)) !== 'win') {
            $file = self::normalizeDirectorySeparator($file);
            $path = self::normalizeDirectorySeparator($path);
        }

        self::assertSame("
RuntimeException : test

at $file : 53
49:         \$application = new Application();
50:         \$output      = new SpyOutput();
51: 
52:         \$application->command('greet', function (\$output): void {
53:             throw new RuntimeException('test');
54:         });
55: 
56:         try {
57:             \$application->run(new StringInput('greet -v'), \$output);
58:         } catch (Throwable \$exception) {

Exception trace:

1   RuntimeException::__construct(\"test\")
    $file : 53

2   Viserio\Component\Console\Application::Viserio\Component\Exception\Tests\Console\{closure}(Object(Viserio\Component\Console\Output\SpyOutput))
    $path\\vendor\php-di\invoker\src\Invoker.php : 82

    $path\\vendor\php-di\invoker\src\Invoker.php : 82

4   Invoker\Invoker::call(Object(Closure))
    $path\\src\Viserio\Component\Support\Invoker.php : 89

5   Viserio\Component\Support\Invoker::call(Object(Closure))
    $path\\src\Viserio\Component\Console\Command\CommandResolver.php : 97
", $output->output);
    }

    public function testRenderWithCommand(): void
    {
        $application = new Application();
        $output      = new SpyOutput();

        $application->add(new ErrorFixtureCommand());

        try {
            $application->run(new StringInput('error -v'), $output);
        } catch (Throwable $exception) {
            $this->handler->render($output, $exception);
        }

        $file = dirname(__DIR__) . '\Fixtures\ErrorFixtureCommand.php';
        $path = $this->rootDir;

        if (\mb_strtolower(\mb_substr(PHP_OS, 0, 3)) !== 'win') {
            $file = self::normalizeDirectorySeparator($file);
            $path = self::normalizeDirectorySeparator($path);
        }

        self::assertSame("
Error : Class 'Viserio\Component\Exception\Tests\Fixtures\Console' not found

at $file : 16
12:     protected static \$defaultName = 'error';\n13: \n14:     public function handle(): void\n15:     {\n16:         Console::test('error');\n17:     }\n18: }
19: 

Exception trace:

1   Error::__construct(\"Class 'Viserio\Component\Exception\Tests\Fixtures\Console' not found\")
    $file : 16

2   Viserio\Component\Exception\Tests\Fixtures\ErrorFixtureCommand::handle()
    $path\\vendor\php-di\invoker\src\Invoker.php : 82

    $path\\vendor\php-di\invoker\src\Invoker.php : 82

4   Invoker\Invoker::call([])
    $path\\src\Viserio\Component\Support\Invoker.php : 89

5   Viserio\Component\Support\Invoker::call()
    $path\\src\Viserio\Component\Console\Command\Command.php : 473
", $output->output);
    }

    public function testRenderWithCommandNoFound(): void
    {
        $application = new Application();
        $output      = new SpyOutput();

        try {
            $application->run(new StringInput('error -v'), $output);
        } catch (Throwable $exception) {
            $this->handler->render($output, $exception);
        }

        $viserioFile = $this->rootDir . '\src\Viserio\Component\Console\Application.php';
        $vendorFile  = $this->rootDir . '\vendor\symfony\console\Application.php';
        $handlerFile = $this->rootDir . '\src\Viserio\Component\Exception\Tests\Console\HandlerTest.php';

        if (\mb_strtolower(\mb_substr(PHP_OS, 0, 3)) !== 'win') {
            $viserioFile = self::normalizeDirectorySeparator($viserioFile);
            $vendorFile  = self::normalizeDirectorySeparator($vendorFile);
            $handlerFile = self::normalizeDirectorySeparator($handlerFile);
        }

        self::assertSame("
Symfony\Component\Console\Exception\CommandNotFoundException : Command \"error\" is not defined.

at $vendorFile : 597
593:                 }
594:                 \$message .= implode(\"\\n    \", \$alternatives);
595:             }
596: 
597:             throw new CommandNotFoundException(\$message, \$alternatives);
598:         }
599: 
600:         // filter out aliases for commands which are already on the list
601:         if (count(\$commands) > 1) {
602:             \$commandList = \$this->commandLoader ? array_merge(array_flip(\$this->commandLoader->getNames()), \$this->commands) : \$this->commands;

Exception trace:

1   Symfony\Component\Console\Exception\CommandNotFoundException::__construct(\"Command \"error\" is not defined.\")
    $vendorFile : 597

2   Symfony\Component\Console\Application::find(\"error\")
    $vendorFile : 206

3   Symfony\Component\Console\Application::doRun(Object(Symfony\Component\Console\Input\StringInput), Object(Viserio\Component\Console\Output\SpyOutput))
    $viserioFile : 300

4   Viserio\Component\Console\Application::run(Object(Symfony\Component\Console\Input\StringInput), Object(Viserio\Component\Console\Output\SpyOutput))
    $handlerFile : 155

5   Viserio\Component\Exception\Tests\Console\HandlerTest::testRenderWithCommandNoFound()
    [internal] : 0
", $output->output);
    }

    /**
     * Returns the vendor path.
     *
     * @return string
     */
    private function getVendorPath(): string
    {
        if ($this->rootDir === null) {
            $reflection = new ReflectionObject($this);
            $dir        = \dirname($reflection->getFileName());

            while (! \is_dir($dir . '/vendor')) {
                $dir = \dirname($dir);
            }

            $this->rootDir = $dir;
        }

        return $this->rootDir . '/vendor/';
    }
}
