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

namespace Viserio\Component\Foundation\Tests;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Foundation\Bootstrap\ShellVerbosityBootstrap;
use Viserio\Component\Foundation\BootstrapManager;
use Viserio\Contract\Foundation\Kernel as KernelContract;

/**
 * @internal
 *
 * @small
 */
final class BootstrapManagerTest extends MockeryTestCase
{
    public function testBootstrapWith(): void
    {
        $kernel = Mockery::mock(KernelContract::class);
        $kernel->shouldReceive('isDebug')
            ->once()
            ->andReturnFalse();

        $boot = new BootstrapManager($kernel);

        self::assertFalse($boot->hasBeenBootstrapped());

        $boot->bootstrapWith([ShellVerbosityBootstrap::class]);

        self::assertTrue($boot->hasBeenBootstrapped());
    }

    public function testAfterAndBeforeBootstrap(): void
    {
        $_SERVER['test'] = 0;

        $kernel = Mockery::mock(KernelContract::class);
        $kernel->shouldReceive('isDebug')
            ->once()
            ->andReturnFalse();

        $boot = new BootstrapManager($kernel);

        $boot->addBeforeBootstrapping(ShellVerbosityBootstrap::class, static function (): void {
            $_SERVER['test'] = 1;
        });

        $boot->addAfterBootstrapping(ShellVerbosityBootstrap::class, static function (): void {
            $_SERVER['test'] = 3;
        });

        $boot->bootstrapWith([ShellVerbosityBootstrap::class]);

        self::assertTrue($boot->hasBeenBootstrapped());
        self::assertSame(3, $_SERVER['test']);

        unset($_SERVER['test']);
    }
}
