<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Tests\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Command\ClosureCommand;
use Viserio\Component\Console\Tests\Fixture\SpyOutput;

class ClosureCommandTest extends TestCase
{
    /**
     * @var \Viserio\Component\Console\Application
     */
    private $application;

    public function setUp(): void
    {
        $this->application = new Application('1.0.0');
    }

    public function testCommand(): void
    {
        $command = new ClosureCommand('demo', function (): void {
            $this->comment('hello');
        });

        $this->application->add($command);

        self::assertSame($command, $this->application->get('demo'));
        self::assertOutputIs('demo', 'hello' . "\n");
    }

    public function testCommandWithParam(): void
    {
        $this->application->setContainer(new ArrayContainer([
            'name' => ' daniel',
        ]));
        $command = new ClosureCommand('demo', function ($name): void {
            $this->comment('hello' . $name);
        });

        $this->application->add($command);

        self::assertSame($command, $this->application->get('demo'));
        self::assertOutputIs('demo', 'hello daniel' . "\n");
    }

    /**
     * @param string $command
     * @param string $expected
     */
    private function assertOutputIs($command, $expected): void
    {
        $output = new SpyOutput();

        $this->application->run(new StringInput($command), $output);

        self::assertEquals($expected, $output->output);
    }
}
