<?php
namespace Viserio\Filesystem\Tests\Adapters;

use League\Flysystem\Sftp\SftpAdapter;
use Viserio\Filesystem\Adapters\SftpConnector;

class SftpConnectorTest extends \PHPUnit_Framework_TestCase
{
    public function testConnect()
    {
        $connector = new SftpConnector();

        $return = $connector->connect([
            'host'     => 'sftp.example.com',
            'port'     => 22,
            'username' => 'your-username',
            'password' => 'your-password',
        ]);

        $this->assertInstanceOf(SftpAdapter::class, $return);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The sftp connector requires host configuration.
     */
    public function testConnectWithoutHost()
    {
        $connector = new SftpConnector();

        $connector->connect([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The sftp connector requires port configuration.
     */
    public function testConnectWithoutPort()
    {
        $connector = new SftpConnector();

        $connector->connect([
            'host'     => 'ftp.example.com',
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The sftp connector requires username configuration.
     */
    public function testConnectWithoutUsername()
    {
        $connector = new SftpConnector();

        $connector->connect([
            'host'     => 'ftp.example.com',
            'port'     => 21,
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The sftp connector requires password or privateKey configuration.
     */
    public function testConnectWithoutPassword()
    {
        $connector = new SftpConnector();

        $connector->connect([
            'host'     => 'ftp.example.com',
            'port'     => 21,
            'username' => 'your-username',
        ]);
    }
}
