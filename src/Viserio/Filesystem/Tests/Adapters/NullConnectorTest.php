<?php
namespace Viserio\Filesystem\Tests\Adapters;

use League\Flysystem\Adapter\NullAdapter;
use Viserio\Filesystem\Adapters\NullConnector;

class NullConnectorTest extends \PHPUnit_Framework_TestCase
{
    public function testConnect()
    {
        $connector = new NullConnector();

        $return = $connector->connect([]);

        $this->assertInstanceOf(NullAdapter::class, $return);
    }
}
