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

namespace Viserio\Component\Profiler\Tests\DataCollector;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Viserio\Component\Profiler\DataCollector\TimeDataCollector;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class TimeDataCollectorTest extends MockeryTestCase
{
    public function testGetMenuAndPosition(): void
    {
        $collect = $this->getTimeDataCollector();
        $data = $collect->getData();

        self::assertSame('right', $collect->getMenuPosition());
        self::assertSame(
            [
                'icon' => 'ic_schedule_white_24px.svg',
                'label' => '',
                'value' => $data['duration_str'],
            ],
            $collect->getMenu()
        );
    }

    public function testGetRequestDuration(): void
    {
        $collect = $this->getTimeDataCollector();
        $data = $collect->getData();

        self::assertSame($data['duration'], $collect->getRequestDuration());

        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getHeaderLine')
            ->once()
            ->with('request_time_float')
            ->andReturn('');
        $request->shouldReceive('getHeaderLine')
            ->once()
            ->with('request_time')
            ->andReturn('');
        $collect = new TimeDataCollector($request);

        self::assertIsFloat($collect->getRequestDuration());
    }

    public function testStartHasStopMeasure(): void
    {
        $collect = $this->getTimeDataCollector();

        $collect->startMeasure('test');

        self::assertTrue($collect->hasStartedMeasure('test'));

        $collect->stopMeasure('test');

        $measure = $collect->getMeasures()[0];

        self::assertSame('test', $measure['label']);

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
            self::assertArrayHasKey($value, $measure);
        }
    }

    public function testStopMeasureThrowsException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed stopping measure [dontexist] because it hasn\'t been started.');

        $collect = $this->getTimeDataCollector();
        $collect->stopMeasure('dontexist');
    }

    private function getTimeDataCollector()
    {
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getHeaderLine')
            ->once()
            ->with('request_time_float')
            ->andReturn('');
        $request->shouldReceive('getHeaderLine')
            ->once()
            ->with('request_time')
            ->andReturn('');
        $collect = new TimeDataCollector($request);
        $collect->collect($request, Mockery::mock(ResponseInterface::class));

        return $collect;
    }
}
