<?php
namespace Viserio\Filesystem\Tests\Adapters;

use League\Flysystem\Dropbox\DropboxAdapter;
use Viserio\Filesystem\Adapters\DropboxConnector;

class DropboxConnectorTest extends \PHPUnit_Framework_TestCase
{
    public function testConnectStandard()
    {
        $connector = new DropboxConnector();

        $return = $connector->connect([
            'token'  => 'your-token',
            'app'    => 'your-app',
        ]);

        $this->assertInstanceOf(DropboxAdapter::class, $return);
    }

    public function testConnectWithPrefix()
    {
        $connector = new DropboxConnector();

        $return = $connector->connect([
            'token'  => 'your-token',
            'app'    => 'your-app',
            'prefix' => 'your-prefix',
        ]);

        $this->assertInstanceOf(DropboxAdapter::class, $return);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The dropbox connector requires authentication.
     */
    public function testConnectWithoutToken()
    {
        $connector = new DropboxConnector();

        $connector->connect(['app' => 'your-app']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The dropbox connector requires authentication.
     */
    public function testConnectWithoutSecret()
    {
        $connector = new DropboxConnector();

        $connector->connect(['token' => 'your-token']);
    }
}
