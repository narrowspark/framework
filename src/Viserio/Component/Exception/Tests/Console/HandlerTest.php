<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Console;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Input\StringInput;
use Throwable;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Tests\Fixture\SpyOutput;
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
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->handler = new Handler();
    }

    public function testRenderWithStringCommand()
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

        if (\mb_strtolower(\mb_substr(PHP_OS, 0, 3)) !== 'win') {
            $file = self::normalizeDirectorySeparator($file);
        }

        self::assertSame("Symfony\Component\Debug\Exception\FatalThrowableError : test

at $file : 40
36:         \$application = new Application();
37:         \$output      = new SpyOutput();
38: 
39:         \$application->command('greet', function (\$output): void {
40:             throw new RuntimeException('test');
41:         });
42: 
43:         try {
44:             \$application->run(new StringInput('greet -v'), \$output);
45:         } catch (Throwable \$exception) {

Exception trace:

1   Symfony\Component\Debug\Exception\FatalThrowableError::__construct(\"test\")
    $file : 40

", $output->output);
    }

    public function testRenderWithCommand()
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

        if (\mb_strtolower(\mb_substr(PHP_OS, 0, 3)) !== 'win') {
            $file = self::normalizeDirectorySeparator($file);
        }

        self::assertSame("Symfony\Component\Debug\Exception\FatalThrowableError : Class 'Viserio\Component\Exception\Tests\Fixtures\Console' not found

at $file : 16
12:     protected static \$defaultName = 'error';\n13: \n14:     public function handle()\n15:     {\n16:         Console::test('error');\n17:     }\n18: }
19: 

Exception trace:

1   Symfony\Component\Debug\Exception\FatalThrowableError::__construct(\"Class 'Viserio\Component\Exception\Tests\Fixtures\Console' not found\")
    $file : 16

", $output->output);
    }

    public function testRenderWithCommandNoFound()
    {
        $application = new Application();
        $output      = new SpyOutput();

        try {
            $application->run(new StringInput('error -v'), $output);
        } catch (Throwable $exception) {
            $this->handler->render($output, $exception);
        }

        $viserioFile = dirname(__DIR__, 6) . '\src\Viserio\Component\Console\Application.php';
        $vendorFile  = dirname(__DIR__, 6) . '\vendor\symfony\console\Application.php';
        $handlerFile = dirname(__DIR__, 6) . '\src\Viserio\Component\Exception\Tests\Console\HandlerTest.php';

        if (\mb_strtolower(\mb_substr(PHP_OS, 0, 3)) !== 'win') {
            $viserioFile = self::normalizeDirectorySeparator($viserioFile);
            $vendorFile  = self::normalizeDirectorySeparator($vendorFile);
            $handlerFile = self::normalizeDirectorySeparator($handlerFile);
        }

        self::assertSame("Symfony\Component\Console\Exception\CommandNotFoundException : Command \"error\" is not defined.

at $vendorFile : 615
611:                 }
612:                 \$message .= implode(\"\\n    \", \$alternatives);
613:             }
614: 
615:             throw new CommandNotFoundException(\$message, \$alternatives);
616:         }
617: 
618:         // filter out aliases for commands which are already on the list
619:         if (count(\$commands) > 1) {
620:             \$commandList = \$this->commandLoader ? array_merge(array_flip(\$this->commandLoader->getNames()), \$this->commands) : \$this->commands;

Exception trace:

1   Symfony\Component\Console\Exception\CommandNotFoundException::__construct(\"Command \"error\" is not defined.\")
    $vendorFile : 615

2   Symfony\Component\Console\Application::find(\"error\")
    $vendorFile : 212

3   Symfony\Component\Console\Application::doRun(Object(Symfony\Component\Console\Input\StringInput), Object(Viserio\Component\Console\Tests\Fixture\SpyOutput))
    $viserioFile : 296

4   Viserio\Component\Console\Application::run(Object(Symfony\Component\Console\Input\StringInput), Object(Viserio\Component\Console\Tests\Fixture\SpyOutput))
    $handlerFile : 116

5   Viserio\Component\Exception\Tests\Console\HandlerTest::testRenderWithCommandNoFound()
    [internal] : 0
", $output->output);
    }
}
