<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapter;

use League\Flysystem\Adapter\NullAdapter;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\NullConnector;

class NullConnectorTest extends TestCase
{
    public function testConnect()
    {
        $connector = new NullConnector();

        $return = $connector->connect([]);

        self::assertInstanceOf(NullAdapter::class, $return);
    }
}
