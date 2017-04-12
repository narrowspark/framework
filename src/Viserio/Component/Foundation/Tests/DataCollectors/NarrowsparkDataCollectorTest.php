<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\DataCollectors;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Foundation\Kernel;
use Viserio\Component\Foundation\DataCollectors\NarrowsparkDataCollector;

class NarrowsparkDataCollectorTest extends MockeryTestCase
{
    public function testGetMenuAndPosition()
    {
        $collect = new NarrowsparkDataCollector();

        static::assertSame(
            [
                'icon'  => 'ic_narrowspark_white_24px.svg',
                'label' => '',
                'value' => Application::VERSION,
            ],
            $collect->getMenu()
        );
        static::assertSame('right', $collect->getMenuPosition());
    }

    public function testGetTooltip()
    {
        $collect = new NarrowsparkDataCollector();
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getHeaderLine')
            ->once()
            ->with('X-Debug-Token');
        $collect->collect(
            $request,
            $this->mock(ResponseInterface::class)
        );
        $xdebug  = extension_loaded('xdebug') ? 'status-green' : 'status-red';
        $opcache = (extension_loaded('Zend OPcache') && ini_get('opcache.enable')) ? 'status-green' : 'status-red';
        $version = Application::VERSION;

        static::assertSame('<div class="webprofiler-menu-tooltip-group"><div class="webprofiler-menu-tooltip-group-piece"><b>Profiler token</b><span></span></div><div class="webprofiler-menu-tooltip-group-piece"><b>Application name</b><span></span></div><div class="webprofiler-menu-tooltip-group-piece"><b>Environment</b><span>develop</span></div><div class="webprofiler-menu-tooltip-group-piece"><b>Debug</b><span class="status-red">disabled</span></div></div><div class="webprofiler-menu-tooltip-group"><div class="webprofiler-menu-tooltip-group-piece"><b>PHP version</b><span>' . phpversion() . '</span></div><div class="webprofiler-menu-tooltip-group-piece"><b>Architecture</b><span>' . PHP_INT_SIZE * 8 . '</span></div><div class="webprofiler-menu-tooltip-group-piece"><b>Timezone</b><span>' . date_default_timezone_get() . '</span></div><div class="webprofiler-menu-tooltip-group-piece"><b>PHP Extensions</b><span class="' . $xdebug . '">Xdebug</span><span class="' . $opcache . '">OPcache</span></div><div class="webprofiler-menu-tooltip-group-piece"><b>PHP SAPI</b><span>cli</span></div></div><div class="webprofiler-menu-tooltip-group"><div class="webprofiler-menu-tooltip-group-piece"><b>Resources</b><span><a href="//narrowspark.de/doc/' . $version . '">Read Narrowspark Doc\'s ' . $version . '</a></span></div><div class="webprofiler-menu-tooltip-group-piece"><b>Help</b><span><a href="//narrowspark.de/support">Narrowspark Support Channels</a></span></div></div>', $collect->getTooltip());
    }
}
