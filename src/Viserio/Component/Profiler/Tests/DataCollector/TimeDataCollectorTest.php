<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\DataCollector;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Profiler\DataCollector\TimeDataCollector;

/**
 * @internal
 */
final class TimeDataCollectorTest extends MockeryTestCase
{
    public function testGetMenuAndPosition(): void
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

    public function testGetRequestDuration(): void
    {
        $collect = $this->getTimeDataCollector();
        $data    = $collect->getData();

        static::assertSame($data['duration'], $collect->getRequestDuration());

        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getHeaderLine')
            ->once()
            ->with('request_time_float')
            ->andReturn('');
        $request->shouldReceive('getHeaderLine')
            ->once()
            ->with('request_time')
            ->andReturn('');
        $collect = new TimeDataCollector($request);

        static::assertInternalType('float', $collect->getRequestDuration());
    }

    public function testStartHasStopMeasure(): void
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
            static::assertArrayHasKey($value, $measure);
        }
    }

    public function testStopMeasureThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed stopping measure [dontexist] because it hasn\'t been started.');

        $collect = $this->getTimeDataCollector();
        $collect->stopMeasure('dontexist');
    }

    private function getTimeDataCollector()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getHeaderLine')
            ->once()
            ->with('request_time_float')
            ->andReturn('');
        $request->shouldReceive('getHeaderLine')
            ->once()
            ->with('request_time')
            ->andReturn('');
        $collect = new TimeDataCollector($request);
        $collect->collect($request, $this->mock(ResponseInterface::class));

        return $collect;
    }
}
