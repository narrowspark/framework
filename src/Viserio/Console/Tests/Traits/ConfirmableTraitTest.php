<?php
declare(strict_types=1);
namespace Viserio\Console\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Symfony\Component\Console\Input\StringInput;
use Viserio\Console\Application;
use Viserio\Console\Tests\Fixture\SpyOutput;
use Viserio\Console\Tests\Fixture\ViserioConfirmableFalseCommand;
use Viserio\Console\Tests\Fixture\ViserioConfirmableTrueCommand;

class ConfirmableTraitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Application
     */
    private $application;

    public function setUp()
    {
        $container = new ArrayContainer([
            'env' => 'production',
        ]);

        $this->application = new Application($container, '1.0.0');
    }

    public function testConfirmableCommandWithTrue()
    {
        $this->application->add(new ViserioConfirmableTrueCommand());

        $this->assertOutputIs(
            'confirmable',
            "**********************************************
*     Application is in Production mode!     *
**********************************************

"
        );
    }

    public function testConfirmableCommandWithFalse()
    {
        $this->application->add(new ViserioConfirmableFalseCommand());

        $this->assertOutputIs(
            'confirmable',
            "**********************************************
*     Application is in Production mode!     *
**********************************************

Command Cancelled!
"
        );
    }

    public function testConfirmableCommandWithFalseAndForce()
    {
        $this->application->add(new ViserioConfirmableFalseCommand());

        $this->assertOutputIs(
            'confirmable --force',
            null
        );
    }
    /**
     * @param string $command
     * @param string $expected
     */
    private function assertOutputIs(string $command, $expected)
    {
        $output = new SpyOutput();

        $this->application->run(new StringInput($command), $output);

        $this->assertEquals($expected, $output->output);
    }
}
