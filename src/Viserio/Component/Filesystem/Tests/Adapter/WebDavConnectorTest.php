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
        $connector = new WebDavConnector();

        $return = $connector->connect([
            'baseUri'  => 'http://example.org/dav/',
            'userName' => 'your-username',
            'password' => 'your-password',
        ]);

        $this->assertInstanceOf(WebDAVAdapter::class, $return);
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

        $this->assertInstanceOf(WebDAVAdapter::class, $return);
    }

    public function testConnectWithoutBaseUri(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A baseUri must be provided.');

        $connector = new WebDavConnector();

        $connector->connect([
            'userName' => 'your-username',
            'password' => 'your-password',
        ]);
    }
}
