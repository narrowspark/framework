<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapter;

use League\Flysystem\WebDAV\WebDAVAdapter;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\WebDavConnector;

/**
 * @internal
 */
final class WebDavConnectorTest extends TestCase
{
    public function testConnect(): void
    {
        $connector = new WebDavConnector([
            'auth' => [
                'baseUri'  => 'http://example.org/dav/',
            ],
            'userName' => 'your-username',
            'password' => 'your-password',
        ]);

        $return = $connector->connect();

        $this->assertInstanceOf(WebDAVAdapter::class, $return);
    }

    public function testConnectWithPrefix(): void
    {
        $connector = new WebDavConnector([
            'auth' => [
                'baseUri'  => 'http://example.org/dav/',
            ],
            'userName' => 'your-username',
            'password' => 'your-password',
            'prefix'   => 'your-prefix',
        ]);

        $return = $connector->connect();

        $this->assertInstanceOf(WebDAVAdapter::class, $return);
    }
}
