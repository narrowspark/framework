<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Tests\DataCollectors;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\WebProfiler\DataCollectors\PhpInfoDataCollector;

class PhpInfoDataCollectorTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testCollect()
    {
        $collect = new PhpInfoDataCollector();
        $collect->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        static::assertRegExp('~^' . preg_quote($collect->getPhpVersion(), '~') . '~', PHP_VERSION);
        static::assertRegExp('~' . preg_quote((string) $collect->getPhpVersionExtra(), '~') . '$~', PHP_VERSION);
        static::assertSame(PHP_INT_SIZE * 8, $collect->getPhpArchitecture());
        static::assertSame(date_default_timezone_get(), $collect->getPhpTimezone());
    }

    public function testGetMenu()
    {
        $collect = new PhpInfoDataCollector();
        $collect->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        static::assertSame(
            [
                'label' => 'PHP Version',
                'value' => PHP_VERSION,
            ],
            $collect->getMenu()
        );
    }
}
