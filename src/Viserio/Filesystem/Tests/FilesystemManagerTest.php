<?php
namespace Viserio\Filesystem\Tests;

use Viserio\Filesystem\FilesystemManager;
use Viserio\Filesystem\Adapters;
use Narrowspark\TestingHelper\Traits\MockeryTrait;

class FilesystemManagerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    protected function getManager($arr)
    {
        $config  = $this->mock('Viserio\Contracts\Config\Manager')
            ->shouldReceive('get')
            ->once()
            ->with('filesystems')
            ->andReturn($arr);

        return new FilesystemManager($config);
    }

    public function testAwsS3ConnectorDriver()
    {
        $manager = $this->getManager([]);

        $this->assertInstanceOf(Adapters\AwsS3Connector::class, $manager->driver('awss3', []));
    }

    public function testDropboxConnectorDriver()
    {
        $manager = $this->getManager([]);

        $this->assertInstanceOf(Adapters\DropboxConnector::class, $manager->driver('dropbox', []));
    }

    public function testFtpConnectorDriver()
    {
        $manager = $this->getManager([]);

        $this->assertInstanceOf(Adapters\FtpConnector::class, $manager->driver('ftp', []));
    }

    public function testGridFSConnectorDriver()
    {
        $manager = $this->getManager([]);

        $this->assertInstanceOf(Adapters\GridFSConnector::class, $manager->driver('gridfs', []));
    }

    public function testLocalConnectorDriver()
    {
        $config = ['driver' => 'local', 'path' => __DIR__];

        $manager = $this->getManager([]);

        $this->assertInstanceOf(Adapters\LocalConnector::class, $manager->driver('local', $config));
    }

    public function testNullConnectorDriver()
    {
        $manager = $this->getManager([]);

        $this->assertInstanceOf(Adapters\NullConnector::class, $manager->driver('null', []));
    }

    public function testRackspaceConnectorDriver()
    {
        $manager = $this->getManager([]);

        $this->assertInstanceOf(Adapters\RackspaceConnector::class, $manager->driver('rackspace', []));
    }

    public function testSftpConnectorDriver()
    {
        $manager = $this->getManager([]);

        $this->assertInstanceOf(Adapters\SftpConnector::class, $manager->driver('sftp', []));
    }

    public function testVfsConnectorDriver()
    {
        $manager = $this->getManager([]);

        $this->assertInstanceOf(Adapters\VfsConnector::class, $manager->driver('vfs', []));
    }

    public function testWebDavConnectorDriver()
    {
        $manager = $this->getManager([]);

        $this->assertInstanceOf(Adapters\WebDavConnector::class, $manager->driver('webdav', []));
    }

    public function testZipConnectorDriver()
    {
        $manager = $this->getManager([]);

        $this->assertInstanceOf(Adapters\ZipConnector::class, $manager->driver('zip', []));
    }
}
