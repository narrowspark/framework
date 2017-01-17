<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapters;

use League\Flysystem\Adapter\Ftp;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapters\FtpConnector;

class FtpConnectorTest extends TestCase
{
    public function testConnect()
    {
        if (! defined('FTP_BINARY')) {
            $this->markTestSkipped('The FTP_BINARY constant is not defined');
        }

        $connector = new FtpConnector();

        $return = $connector->connect([
            'host'     => 'ftp.example.com',
            'port'     => 21,
            'username' => 'your-username',
            'password' => 'your-password',
        ]);

        self::assertInstanceOf(Ftp::class, $return);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The sftp connector requires host configuration.
     */
    public function testConnectWithoutHost()
    {
        $connector = new FtpConnector();

        $connector->connect([]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The sftp connector requires port configuration.
     */
    public function testConnectWithoutPort()
    {
        $connector = new FtpConnector();

        $connector->connect([
            'host' => 'ftp.example.com',
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The sftp connector requires username configuration.
     */
    public function testConnectWithoutUsername()
    {
        $connector = new FtpConnector();

        $connector->connect([
            'host' => 'ftp.example.com',
            'port' => 21,
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The sftp connector requires password configuration.
     */
    public function testConnectWithoutPassword()
    {
        $connector = new FtpConnector();

        $connector->connect([
            'host'     => 'ftp.example.com',
            'port'     => 21,
            'username' => 'your-username',
        ]);
    }
}
