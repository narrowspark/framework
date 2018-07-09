<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\DataCollector;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Profiler\DataCollector\MemoryDataCollector;
use Viserio\Component\Support\Traits\BytesFormatTrait;

/**
 * @internal
 */
final class MemoryDataCollectorTest extends MockeryTestCase
{
    use BytesFormatTrait;

    public function testGetMenu(): void
    {
        $collect = new MemoryDataCollector();
        $collect->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        $data = $collect->getData();

        static::assertSame(
            [
                'icon'  => 'ic_memory_white_24px.svg',
                'label' => $data['memory'] / 1024 / 1024,
                'value' => 'MB',
                'class' => ($data['memory'] / 1024 / 1024) > 50 ? 'yellow' : '',
            ],
            $collect->getMenu()
        );
    }

    public function testGetTooltip(): void
    {
        $collect = new MemoryDataCollector();
        $collect->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        $collect->updateMemoryUsage();
        $data = $collect->getData();

        $memoryLimit = \ini_get('memory_limit') === '-1' ? 'Unlimited' : self::convertToBytes(\ini_get('memory_limit')) / 1024 / 1024;

        static::assertSame(
            '<div class="profiler-menu-tooltip-group"><div class="profiler-menu-tooltip-group-piece"><b>Peak memory usage</b><span>' . $data['memory'] / 1024 / 1024 . ' MB</span></div><div class="profiler-menu-tooltip-group-piece"><b>PHP memory limit</b><span>' . $memoryLimit . ' MB</span></div></div>',
            $collect->getTooltip()
        );
    }
}
