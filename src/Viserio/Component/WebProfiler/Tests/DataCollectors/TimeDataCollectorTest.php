<?php
declare(strict_types=1);
namespace Viserio\Component\WebProfiler\Tests\DataCollectors;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\WebProfiler\DataCollectors\TimeDataCollector;

class TimeDataCollectorTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testGetMenuAndPosition()
    {
        $collect = $this->getTimeDataCollector();
        $data    = $collect->getData();

        static::assertSame('right', $collect->getMenuPosition());
        static::assertSame(
            [
                'icon'  => 'ic_schedule_white_24px.svg',
                'label' => '',
                'value' => $data['duration_str'],
            ],
            $collect->getMenu()
        );
    }

    public function testGetRequestDuration()
    {
        $collect = $this->getTimeDataCollector();
        $data    = $collect->getData();

        static::assertSame($data['duration'], $collect->getRequestDuration());

        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getHeaderLine')
            ->once()
            ->with('REQUEST_TIME_FLOAT')
            ->andReturn('');
        $request->shouldReceive('getHeaderLine')
            ->once()
            ->with('REQUEST_TIME')
            ->andReturn('');
        $collect = new TimeDataCollector($request);

        static::assertTrue(is_float($collect->getRequestDuration()));
    }

    public function testStartHasStopMeasure()
    {
        $collect = $this->getTimeDataCollector();

        $collect->startMeasure('test');

        static::assertTrue($collect->hasStartedMeasure('test'));

        $collect->stopMeasure('test');

        $measure = $collect->getMeasures()[0];

        static::assertSame('test', $measure['label']);

        $keysExistCheck = [
            'label',
            'start',
            'relative_start',
            'end',
            'relative_end',
            'duration',
            'duration_str',
            'params',
            'collector',
        ];

        foreach ($keysExistCheck as $key => $value) {
            static::assertTrue(array_key_exists($value, $measure));
        }
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Failed stopping measure "dontexist" because it hasn't been started.
     */
    public function testStopMeasureThrowsException()
    {
        $collect = $this->getTimeDataCollector();
        $collect->stopMeasure('dontexist');
    }

    private function getTimeDataCollector()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getHeaderLine')
            ->once()
            ->with('REQUEST_TIME_FLOAT')
            ->andReturn('');
        $request->shouldReceive('getHeaderLine')
            ->once()
            ->with('REQUEST_TIME')
            ->andReturn('');
        $collect = new TimeDataCollector($request);
        $collect->collect($request, $this->mock(ResponseInterface::class));

        return $collect;
    }
}
