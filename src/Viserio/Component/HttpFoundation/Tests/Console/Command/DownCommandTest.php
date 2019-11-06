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

namespace Viserio\Component\HttpFoundation\Tests\Console\Command;

use Mockery;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\HttpFoundation\Console\Command\DownCommand;
use Viserio\Component\Support\Invoker;
use Viserio\Contract\Console\Kernel as ConsoleKernelContract;
use const DIRECTORY_SEPARATOR;

/**
 * @internal
 *
 * @small
 */
final class DownCommandTest extends MockeryTestCase
{
    public function testCommand(): void
    {
        $framework = \dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'framework';
        $down = $framework . DIRECTORY_SEPARATOR . 'down';

        @\mkdir($framework);

        $kernel = Mockery::mock(ConsoleKernelContract::class);
        $kernel->shouldReceive('getStoragePath')
            ->once()
            ->with('framework' . DIRECTORY_SEPARATOR . 'down')
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
