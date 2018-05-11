<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapter;

use League\Flysystem\WebDAV\WebDAVAdapter;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\WebDavConnector;

class WebDavConnectorTest extends TestCase
{
    public function testConnect(): void
    {
        $connector = new WebDavConnector();

        $return = $connector->connect([
            'baseUri'  => 'http://example.org/dav/',
            'userName' => 'your-username',
            'password' => 'your-password',
        ]);

        self::assertInstanceOf(WebDAVAdapter::class, $return);
    }

    public function testConnectWithPrefix(): void
    {
        $connector = new WebDavConnector();

        $return = $connector->connect([
            'baseUri'  => 'http://example.org/dav/',
            'userName' => 'your-username',
            'password' => 'your-password',
            'prefix'   => 'your-prefix',
        ]);

        self::assertInstanceOf(WebDAVAdapter::class, $return);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage A baseUri must be provided.
     */
    public function testConnectWithoutBaseUri(): void
    {
        $connector = new WebDavConnector();

        $connector->connect([
            'userName' => 'your-username',
            'password' => 'your-password',
        ]);
    }
}
