<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Tests\Fixture\SpyOutput;
use Viserio\Component\Console\Tests\Fixture\ViserioConfirmableFalseCommand;
use Viserio\Component\Console\Tests\Fixture\ViserioConfirmableTrueCommand;

class ConfirmableTraitTest extends TestCase
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

        $this->application = new Application('1.0.0');
        $this->application->setContainer($container);
    }

    public function testConfirmableCommandWithTrue()
    {
        $this->application->add(new ViserioConfirmableTrueCommand());

        self::assertOutputIs(
            'confirmable',
            '**********************************************
*     Application is in Production mode!     *
**********************************************

'
        );
    }

    public function testConfirmableCommandWithFalse()
    {
        $this->application->add(new ViserioConfirmableFalseCommand());

        self::assertOutputIs(
            'confirmable',
            '**********************************************
*     Application is in Production mode!     *
**********************************************

Command Cancelled!
'
        );
    }

    public function testConfirmableCommandWithFalseAndForce()
    {
        $this->application->add(new ViserioConfirmableFalseCommand());

        self::assertOutputIs(
            'confirmable --force',
            null
        );
    }

    /**
     * @param string      $command
     * @param string|null $expected
     */
    private function assertOutputIs(string $command, $expected)
    {
        $output = new SpyOutput();

        $this->application->run(new StringInput($command), $output);

        self::assertEquals($expected, $output->output);
    }
}
