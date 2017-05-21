<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\DataCollectors;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Profiler\DataCollectors\PhpInfoDataCollector;

class PhpInfoDataCollectorTest extends MockeryTestCase
{
    public function testCollect()
    {
        $collect = new PhpInfoDataCollector();
        $collect->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        self::assertRegExp('~^' . preg_quote($collect->getPhpVersion(), '~') . '~', PHP_VERSION);
        self::assertRegExp('~' . preg_quote((string) $collect->getPhpVersionExtra(), '~') . '$~', PHP_VERSION);
        self::assertSame(PHP_INT_SIZE * 8, $collect->getPhpArchitecture());
        self::assertSame(date_default_timezone_get(), $collect->getPhpTimezone());
    }

    public function testGetMenu()
    {
        $collect = new PhpInfoDataCollector();
        $collect->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        self::assertSame(
            [
                'label' => 'PHP Version',
                'value' => PHP_VERSION,
            ],
            $collect->getMenu()
        );
    }
}
