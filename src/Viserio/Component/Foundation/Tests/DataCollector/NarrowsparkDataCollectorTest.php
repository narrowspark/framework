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

namespace Viserio\Component\Foundation\Tests\DataCollector;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\DataCollector\NarrowsparkDataCollector;

/**
 * @internal
 *
 * @small
 */
final class NarrowsparkDataCollectorTest extends MockeryTestCase
{
    public function testGetMenuAndPosition(): void
    {
        $collect = new NarrowsparkDataCollector();

        self::assertSame(
            [
                'icon' => 'ic_narrowspark_white_24px.svg',
                'label' => '',
                'value' => AbstractKernel::VERSION,
            ],
            $collect->getMenu()
        );
        self::assertSame('right', $collect->getMenuPosition());
    }

    public function testGetTooltip(): void
    {
        $collect = new NarrowsparkDataCollector('develop', false);
        $request = \Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getHeaderLine')
            ->once()
            ->with('x-debug-token');
        $collect->collect(
            $request,
            \Mockery::mock(ResponseInterface::class)
        );
        $xdebug = \extension_loaded('xdebug') ? 'status-green' : 'status-red';
        $opcache = (\extension_loaded('Zend OPcache') && \filter_var(\ini_get('opcache.enable'), \FILTER_VALIDATE_BOOLEAN)) ? 'status-green' : 'status-red';
        $version = AbstractKernel::VERSION;

        self::assertSame('<div class="profiler-menu-tooltip-group"><div class="profiler-menu-tooltip-group-piece"><b>Profiler token</b><span></span></div><div class="profiler-menu-tooltip-group-piece"><b>Application name</b><span></span></div><div class="profiler-menu-tooltip-group-piece"><b>Environment</b><span>develop</span></div><div class="profiler-menu-tooltip-group-piece"><b>Debug</b><span class="status-red">disabled</span></div></div><div class="profiler-menu-tooltip-group"><div class="profiler-menu-tooltip-group-piece"><b>PHP version</b><span>' . \PHP_VERSION . '</span></div><div class="profiler-menu-tooltip-group-piece"><b>Architecture</b><span>' . \PHP_INT_SIZE * 8 . '</span></div><div class="profiler-menu-tooltip-group-piece"><b>Timezone</b><span>' . \date_default_timezone_get() . '</span></div><div class="profiler-menu-tooltip-group-piece"><b>PHP Extensions</b><span class="' . $xdebug . '">Xdebug</span><span class="' . $opcache . '">OPcache</span></div><div class="profiler-menu-tooltip-group-piece"><b>PHP SAPI</b><span>cli</span></div></div><div class="profiler-menu-tooltip-group"><div class="profiler-menu-tooltip-group-piece"><b>Resource</b><span><a href="//narrowspark.de/doc/' . $version . '">Read Narrowspark Doc\'s ' . $version . '</a></span></div><div class="profiler-menu-tooltip-group-piece"><b>Help</b><span><a href="//narrowspark.de/support">Narrowspark Support Channels</a></span></div></div>', $collect->getTooltip());
    }
}
