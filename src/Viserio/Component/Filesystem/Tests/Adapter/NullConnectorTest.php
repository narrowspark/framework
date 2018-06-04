<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapter;

use League\Flysystem\Adapter\NullAdapter;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\NullConnector;

/**
 * @internal
 */
final class NullConnectorTest extends TestCase
{
    public function testConnect(): void
    {
        $connector = new NullConnector();

        $return = $connector->connect([]);

        $this->assertInstanceOf(NullAdapter::class, $return);
    }
}
