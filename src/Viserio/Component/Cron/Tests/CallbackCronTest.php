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

namespace Viserio\Component\Cron\Tests;

use InvalidArgumentException;
use LogicException;
use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Cron\CallbackCron;

/**
 * @internal
 *
 * @small
 */
final class CallbackCronTest extends MockeryTestCase
{
    /**
     * Mocked CacheItemPoolInterface.
     *
     * @var \Mockery\MockInterface|\Psr\Cache\CacheItemPoolInterface
     */
    protected $cache;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = Mockery::mock(CacheItemPoolInterface::class);
    }

    public function testCallbackCronToThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid scheduled callback cron job. Must be string or callable.');

        new CallbackCron(new CallbackCron('tests'));
    }

    public function testWithoutOverlappingToThrowException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('A scheduled cron job description is required to prevent overlapping. Use the \'setDescription\' method before \'withoutOverlapping\'.');

        $cron = new CallbackCron('tests');
        $cron->withoutOverlapping();
    }

    public function testBasicCronCompilation(): void
    {
        $_SERVER['test'] = false;

        $item = Mockery::mock(CacheItemInterface::class);
        $item->shouldReceive('set')
            ->once();
        $item->shouldReceive('expiresAfter')
            ->once()
            ->with(1440);
        $cache = Mockery::mock(CacheItemPoolInterface::class);
        $cache->shouldReceive('getItem')
            ->once()
            ->andReturn($item);
        $cache->shouldReceive('save')
            ->once()
            ->with($item);
        $cache->shouldReceive('deleteItem')
            ->once();

        $cron = new CallbackCron(static function (): void {
            $_SERVER['test'] = true;
        });
        $cron->setCacheItemPool($cache);
        $cron->setPath(__DIR__);

        $cron->run();

        self::assertTrue($_SERVER['test']);

        unset($_SERVER['test']);

        $_SERVER['test'] = false;

        $cron = new CallbackCron(static function (): void {
            $_SERVER['test'] = true;
        });
        $cron->setCacheItemPool($cache);
        $cron->setPath(__DIR__);

        $cron->setDescription('run test')->run();

        self::assertTrue($_SERVER['test']);
        self::assertSame('run test', $cron->getSummaryForDisplay());

        unset($_SERVER['test']);
    }

    public function testCronRunWithoutOverlapping(): void
    {
        $name = 'schedule-' . \sha1('* * * * *test');
        $item = Mockery::mock(CacheItemInterface::class);
        $item->shouldReceive('set')
            ->once()
            ->with($name);
        $item->shouldReceive('expiresAfter')
            ->once()
            ->with(1440);
        $cache = Mockery::mock(CacheItemPoolInterface::class);
        $cache->shouldReceive('getItem')
            ->once()
            ->andReturn($item);
        $cache->shouldReceive('save')
            ->once()
            ->with($item);
        $cache->shouldReceive('deleteItem')
            ->once()
            ->with($name);

        $_SERVER['test'] = false;

        $cron = new CallbackCron(static function (): void {
            $_SERVER['test'] = true;
        });
        $cron->setCacheItemPool($cache)
            ->setDescription('test')
            ->withoutOverlapping()
            ->runInBackground();

        // OK
        $cron->run();

        self::assertTrue($_SERVER['test']);

        unset($_SERVER['test']);
    }

    // TODO: Add before | this is the output of the cron | after test case
}
