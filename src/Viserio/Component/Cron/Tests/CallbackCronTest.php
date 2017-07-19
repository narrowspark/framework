<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Cron\CallbackCron;

class CallbackCronTest extends MockeryTestCase
{
    /**
     * Mocked CacheItemPoolInterface.
     *
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    protected $cache;

    public function setUp(): void
    {
        parent::setUp();

        $this->cache = $this->mock(CacheItemPoolInterface::class);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid scheduled callback cron job. Must be string or callable.
     */
    public function testCallbackCronToThrowException(): void
    {
        new CallbackCron(new CallbackCron('tests'));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage A scheduled cron job description is required to prevent overlapping. Use the 'description' method before 'withoutOverlapping'.
     */
    public function testWithoutOverlappingToThrowException(): void
    {
        $cron = new CallbackCron('tests');
        $cron->withoutOverlapping();
    }

    public function testBasicCronCompilation(): void
    {
        $_SERVER['test'] = false;

        $item = $this->mock(CacheItemInterface::class);
        $item->shouldReceive('set')
            ->once();
        $item->shouldReceive('expiresAfter')
            ->once()
            ->with(1440);
        $cache = $this->mock(CacheItemPoolInterface::class);
        $cache->shouldReceive('getItem')
            ->once()
            ->andReturn($item);
        $cache->shouldReceive('save')
            ->once()
            ->with($item);
        $cache->shouldReceive('deleteItem')
            ->once();

        $cron = new CallbackCron(function (): void {
            $_SERVER['test'] = true;
        });
        $cron->setCacheItemPool($cache);
        $cron->setPath(__DIR__);

        $cron->run();

        self::assertTrue($_SERVER['test']);

        unset($_SERVER['test']);

        $_SERVER['test'] = false;

        $cron = new CallbackCron(function (): void {
            $_SERVER['test'] = true;
        });
        $cron->setCacheItemPool($cache);
        $cron->setPath(__DIR__);

        $cron->setDescription('run test')->run();

        self::assertTrue($_SERVER['test']);
        self::assertSame('run test', $cron->getSummaryForDisplay());

        unset($_SERVER['test']);
    }

    public function testCronRunWithoutOverlappinga(): void
    {
        $name = 'schedule-' . \sha1('* * * * * *' . 'test');
        $item = $this->mock(CacheItemInterface::class);
        $item->shouldReceive('set')
            ->once()
            ->with($name);
        $item->shouldReceive('expiresAfter')
            ->once()
            ->with(1440);
        $cache = $this->mock(CacheItemPoolInterface::class);
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

        $cron = new CallbackCron(function (): void {
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

    //TODO: Add before | this is the output of the cron | after test case
}
