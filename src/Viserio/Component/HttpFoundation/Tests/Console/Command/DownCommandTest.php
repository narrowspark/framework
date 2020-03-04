<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\HttpFoundation\Tests\Console\Command;

use Mockery;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\HttpFoundation\Console\Command\DownCommand;
use Viserio\Component\Support\Invoker;
use Viserio\Contract\Console\Kernel as ConsoleKernelContract;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class DownCommandTest extends MockeryTestCase
{
    public function testCommand(): void
    {
        $framework = \dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'framework';
        $down = $framework . \DIRECTORY_SEPARATOR . 'down';

        @\mkdir($framework);

        $kernel = Mockery::mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('getStoragePath')
            ->once()
            ->with('framework' . \DIRECTORY_SEPARATOR . 'down')
            ->andReturn($down);

        $container = new ArrayContainer([
            ConsoleKernelContract::class => $kernel,
        ]);

        $invoker = new Invoker();
        $invoker->setContainer($container)
            ->injectByTypeHint(true)
            ->injectByParameterName(true);

        $command = new DownCommand();
        $command->setInvoker($invoker);
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute(['--message' => 'test', '--retry' => 1]);

        $output = $tester->getDisplay(true);

        self::assertEquals("Application is now in maintenance mode.\n", $output);

        $data = \json_decode(\file_get_contents($down), true);

        self::assertIsInt($data['time']);
        self::assertSame('test', $data['message']);
        self::assertSame(1, $data['retry']);

        @\unlink($down);
        @\rmdir($framework);
    }
}
