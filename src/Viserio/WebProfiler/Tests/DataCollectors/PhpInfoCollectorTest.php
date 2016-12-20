<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Tests\DataCollectors;

use Mockery as Mock;
use Viserio\WebProfiler\DataCollectors\PhpInfoCollector;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class PhpInfoCollectorTest extends \PHPUnit_Framework_TestCase
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
        $collect = new PhpInfoCollector();
        $collect->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        $this->assertRegExp('~^'.preg_quote($collect->getPhpVersion(), '~').'~', PHP_VERSION);
        $this->assertRegExp('~'.preg_quote((string) $collect->getPhpVersionExtra(), '~').'$~', PHP_VERSION);
        $this->assertSame(PHP_INT_SIZE * 8, $collect->getPhpArchitecture());
        $this->assertSame(date_default_timezone_get(), $collect->getPhpTimezone());
    }

    public function testGetMenu()
    {
        $collect = new PhpInfoCollector();
        $collect->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        $this->assertSame(
            [
                'label' => 'PHP Version',
                'value' => PHP_VERSION,
            ],
            $collect->getMenu()
        );
    }
}
