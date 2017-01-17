<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Cron\CallbackCron;

class CallbackCronTest extends TestCase
{
    use MockeryTrait;

    /**
     * Mocked CacheItemPoolInterface.
     *
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    protected $cache;

    public function setUp()
    {
        parent::setUp();

        $this->cache = $this->mock(CacheItemPoolInterface::class);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid scheduled callback cron job. Must be string or callable.
     */
    public function testCallbackCronToThrowException()
    {
        new CallbackCron($this->cache, new CallbackCron($this->cache, 'tests'));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage A scheduled cron job description is required to prevent overlapping. Use the 'description' method before 'withoutOverlapping'.
     */
    public function testWithoutOverlappingToThrowException()
    {
        $cron = new CallbackCron($this->cache, 'tests');
        $cron->withoutOverlapping();
    }

    public function testBasicCronCompilation()
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

        $cron = new CallbackCron($cache, function () {
            $_SERVER['test'] = true;
        });
        $cron->setPath(__DIR__);

        $cron->run();

        self::assertTrue($_SERVER['test']);

        unset($_SERVER['test']);

        $_SERVER['test'] = false;

        $cron = new CallbackCron($cache, function () {
            $_SERVER['test'] = true;
        });
        $cron->setPath(__DIR__);

        $cron->setDescription('run test')->run();

        self::assertTrue($_SERVER['test']);
        self::assertSame('run test', $cron->getSummaryForDisplay());

        unset($_SERVER['test']);
    }
}
