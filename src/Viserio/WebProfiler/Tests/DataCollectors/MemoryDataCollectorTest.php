<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Tests\DataCollectors;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\WebProfiler\DataCollectors\MemoryDataCollector;

class MemoryDataCollectorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testGetMenu()
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

    public function testGetTooltip()
    {
        $collect = new MemoryDataCollector();
        $collect->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        $collect->updateMemoryUsage();
        $data = $collect->getData();

        static::assertSame(
            '<div class="webprofiler-menu-tooltip-group"><div class="webprofiler-menu-tooltip-group-piece"><b>Peak memory usage</b><span>' . $data['memory'] / 1024 / 1024 . ' MB</span></div><div class="webprofiler-menu-tooltip-group-piece"><b>PHP memory limit</b><span>Unlimited MB</span></div></div>',
            $collect->getTooltip()
        );
    }
}
