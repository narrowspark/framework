<?php
namespace Viserio\Filesystem\Tests\Adapters;

use League\Flysystem\WebDAV\WebDAVAdapter;
use Viserio\Filesystem\Adapters\WebDavConnector;

class WebDavConnectorTest extends \PHPUnit_Framework_TestCase
{
    public function testConnect()
    {
        $connector = new WebDavConnector();

        $return = $connector->connect([
            'baseUri'  => 'http://example.org/dav/',
            'userName' => 'your-username',
            'password' => 'your-password',
        ]);

        $this->assertInstanceOf(WebDAVAdapter::class, $return);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The WebDav connector requires baseUri configuration.
     */
    public function testConnectWithoutBaseUri()
    {
        $connector = new WebDavConnector();

        $connector->connect([
            'userName' => 'your-username',
            'password' => 'your-password',
        ]);
    }
}
