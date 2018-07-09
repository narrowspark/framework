<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\DataCollector;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Profiler\DataCollector\PhpInfoDataCollector;

/**
 * @internal
 */
final class PhpInfoDataCollectorTest extends MockeryTestCase
{
    public function testCollect(): void
    {
        $collect = new PhpInfoDataCollector();
        $collect->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        static::assertRegExp('~^' . \preg_quote($collect->getPhpVersion(), '~') . '~', \PHP_VERSION);
        static::assertRegExp('~' . \preg_quote((string) $collect->getPhpVersionExtra(), '~') . '$~', \PHP_VERSION);
        static::assertSame(\PHP_INT_SIZE * 8, $collect->getPhpArchitecture());
        static::assertSame(\date_default_timezone_get(), $collect->getPhpTimezone());
    }

    public function testGetMenu(): void
    {
        $collect = new PhpInfoDataCollector();
        $collect->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        static::assertSame(
            [
                'label' => 'PHP Version',
                'value' => \PHP_VERSION,
            ],
            $collect->getMenu()
        );
    }
}
