<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Tests\Adapters;

use League\Flysystem\Adapter\NullAdapter;
use PHPUnit\Framework\TestCase;
use Viserio\Filesystem\Adapters\NullConnector;

class NullConnectorTest extends TestCase
{
    public function testConnect()
    {
        $connector = new NullConnector();

        $return = $connector->connect([]);

        self::assertInstanceOf(NullAdapter::class, $return);
    }
}
