<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Console;

use RuntimeException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Throwable;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Tests\Fixture\SpyOutput;
use Viserio\Component\Exception\Console\Handler;

class HandlerTest extends TestCase
{
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

    public function testRender()
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

        $dir = __DIR__;

        self::assertSame("Symfony\Component\Debug\Exception\FatalThrowableError : test

at $dir\HandlerTest.php: 36
32:         \$application = new Application();
33:         \$output      = new SpyOutput();
34: 
35:         \$application->command('greet', function (\$output): void {
36:             throw new RuntimeException('test');
37:         });
38: 
39:         try {
40:             \$application->run(new StringInput('greet -v'), \$output);
41:         } catch (Throwable \$exception) {

Exception trace:

1   Symfony\Component\Debug\Exception\FatalThrowableError::__construct(\"test\")

    $dir\HandlerTest.php : 36
", $output->output);
    }
}
