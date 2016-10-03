<?php
declare(strict_types=1);
namespace Viserio\Console\Tests\Command;

use Viserio\Console\Command\ClosureCommand;
use Narrowspark\TestingHelper\ArrayContainer;
use Symfony\Component\Console\Input\StringInput;
use Viserio\Console\Application;
use Viserio\Events\Dispatcher;
use Viserio\Console\Tests\Fixture\SpyOutput;
use Viserio\Support\Invoker;

class ClosureCommandTest extends \PHPUnit_Framework_TestCase
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

        $this->application = new Application($container, new Dispatcher($container), '1.0.0');

        $this->invoker = (new Invoker())
            ->injectByTypeHint(true)
            ->injectByParameterName(true)
            ->setContainer($this->application->getContainer());
    }

    public function testCommand()
    {
        $command = new ClosureCommand('demo', function () {
            return 'hello';
        });

        $this->application->add($command);

        $this->assertSame($command, $this->application->get('demo'));
    }

    /**
     * @param string $command
     * @param string $expected
     */
    private function assertOutputIs($command, $expected)
    {
        $output = new SpyOutput();

        $this->application->run(new StringInput($command), $output);

        $this->assertEquals($expected, $output->output);
    }
}
