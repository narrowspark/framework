<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Console\Tests\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\StringInput;
use Viserio\Component\Console\Application;
use Viserio\Component\Console\Command\ClosureCommand;
use Viserio\Component\Console\Output\SpyOutput;

/**
 * @internal
 *
 * @small
 */
final class ClosureCommandTest extends TestCase
{
    /** @var \Viserio\Component\Console\Application */
    private $application;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->application = new Application();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($_ENV['SHELL_VERBOSITY'], $_GET['SHELL_VERBOSITY'], $_SERVER['SHELL_VERBOSITY']);
    }

    public function testCommand(): void
    {
        $command = new ClosureCommand('demo', function (): void {
            $this->comment('hello');
        });

        $this->application->add($command);

        self::assertSame($command, $this->application->get('demo'));
        $this->assertOutputIs('demo', 'hello' . "\n");
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
        $this->assertOutputIs('demo', 'hello daniel' . "\n");
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
