<?php
namespace Viserio\Filesystem\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Filesystem\FilesystemAdapter;
use Viserio\Filesystem\FilesystemManager;

class FilesystemManagerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    protected function getManager($arr)
    {
        $config = $this->mock('Viserio\Contracts\Config\Manager');
        $config->shouldReceive('get')->once()->with('filesystems')->andReturn($arr);

        return new FilesystemManager($config);
    }

    public function testAwsS3ConnectorDriver()
    {
        $manager = $this->getManager([]);

        $this->assertInstanceOf(FilesystemAdapter::class, $manager->driver('awss3', ['host' => 'localhost']));
    }

    public function testDropboxConnectorDriver()
    {
        $manager = $this->getManager([]);

        $this->assertInstanceOf(FilesystemAdapter::class, $manager->driver('dropbox', ['host' => 'localhost']));
    }

    public function testFtpConnectorDriver()
    {
        $manager = $this->getManager([]);

        $this->assertInstanceOf(FilesystemAdapter::class, $manager->driver('ftp', ['host' => 'localhost']));
    }

    public function testGridFSConnectorDriver()
    {
        $manager = $this->getManager([]);

        $this->assertInstanceOf(FilesystemAdapter::class, $manager->driver('gridfs', ['host' => 'localhost']));
    }

    public function testLocalConnectorDriver()
    {
        $config = ['driver' => 'local', 'path' => __DIR__];

        $manager = $this->getManager([]);

        $this->assertInstanceOf(FilesystemAdapter::class, $manager->driver('local', $config));
    }

    public function testNullConnectorDriver()
    {
        $manager = $this->getManager([]);

        $this->assertInstanceOf(FilesystemAdapter::class, $manager->driver('null', ['host' => 'localhost']));
    }

    public function testRackspaceConnectorDriver()
    {
        $manager = $this->getManager([]);

        $this->assertInstanceOf(FilesystemAdapter::class, $manager->driver('rackspace', ['host' => 'localhost']));
    }

    public function testSftpConnectorDriver()
    {
        $manager = $this->getManager([]);

        $this->assertInstanceOf(FilesystemAdapter::class, $manager->driver('sftp', ['host' => 'localhost']));
    }

    public function testVfsConnectorDriver()
    {
        $manager = $this->getManager([]);

        $this->assertInstanceOf(FilesystemAdapter::class, $manager->driver('vfs', ['host' => 'localhost']));
    }

    public function testWebDavConnectorDriver()
    {
        $manager = $this->getManager([]);

        $this->assertInstanceOf(FilesystemAdapter::class, $manager->driver('webdav', ['host' => 'localhost']));
    }

    public function testZipConnectorDriver()
    {
        $manager = $this->getManager([]);

        $this->assertInstanceOf(FilesystemAdapter::class, $manager->driver('zip', ['host' => 'localhost']));
    }
}
