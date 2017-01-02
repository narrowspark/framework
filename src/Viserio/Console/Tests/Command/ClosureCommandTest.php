<?php
declare(strict_types=1);
namespace Viserio\Console\Tests\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use Symfony\Component\Console\Input\StringInput;
use Viserio\Console\Application;
use Viserio\Console\Command\ClosureCommand;
use Viserio\Console\Tests\Fixture\SpyOutput;
use Viserio\Support\Invoker;
use PHPUnit\Framework\TestCase;

class ClosureCommandTest extends TestCase
{
    /**
     * @var Application
     */
    private $application;

    /**
     * @var Invoker
     */
    private $invoker;

    public function setUp()
    {
        $container = new ArrayContainer([]);

        $this->application = new Application($container, '1.0.0');

        $this->invoker = (new Invoker())
            ->injectByTypeHint(true)
            ->injectByParameterName(true)
            ->setContainer($this->application->getContainer());
    }

    public function testCommand()
    {
        $command = new ClosureCommand('demo', function () {
            $this->comment('hello');
        });

        $this->application->add($command);

        self::assertSame($command, $this->application->get('demo'));
        self::assertOutputIs('demo', 'hello' . "\n");
    }

    /**
     * @param string $command
     * @param string $expected
     */
    private function assertOutputIs($command, $expected)
    {
        $output = new SpyOutput();

        $this->application->run(new StringInput($command), $output);

        self::assertEquals($expected, $output->output);
    }
}
