<?php
namespace Viserio\Filesystem\Tests;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\CurlException;
use MongoConnectionException;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\Config\Manager as ConfigManger;
use Viserio\Filesystem\FilesystemAdapter;
use Viserio\Filesystem\FilesystemManager;

class FilesystemManagerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    protected function getManager()
    {
        $config = $this->mock(ConfigManger::class);

        return new FilesystemManager($config);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The driver [notfound] is not supported.
     */
    public function testDriverToThrowException()
    {
        $manager = $this->getManager();
        $manager->driver('notfound');
    }

    // public function testSetAndGetDefaultDriver()
    // {
    //     $manager = $this->getManager();

    //     $manager->getConfig()->shouldReceive('set')->once()
    //         ->with('flysystem::default')->withArgs(['localfly']);

    //     $manager->getConfig()->shouldReceive('get')->once()
    //         ->with('flysystem::default')->andReturn('localfly');

    //     $manager->setDefaultDriver('localfly');

    //     $this->assertTrue($manager->getDefaultDriver());
    // }

    // public function testGetDefaultDriverFromConfig()
    // {
    //     $manager = $this->getManager();

    //     $manager->getConfig()->shouldReceive('get')->once()
    //         ->with('flysystem::default')->withArgs(['local'])->andReturn('local');

    //     $this->assertSame('local', $manager->getDefaultDriver());
    // }

    public function testAwsS3ConnectorDriver()
    {
        if (defined('HHVM_VERSION') && version_compare(HHVM_VERSION, '3.9.0') < 0) {
            $this->markTestSkipped('The AWS SDK requires a newer verison of HHVM');
        }

        $manager = $this->getManager();

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->driver(
                'awss3',
                [
                    'key'     => 'your-key',
                    'secret'  => 'your-secret',
                    'bucket'  => 'your-bucket',
                    'region'  => 'us-east-1',
                    'version' => 'latest',
                ]
            )
        );
    }

    public function testDropboxConnectorDriver()
    {
        $manager = $this->getManager();

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->driver(
                'dropbox',
                [
                    'token' => 'your-token',
                    'app'   => 'your-app',
                ]
            )
        );
    }

    public function testFtpConnectorDriver()
    {
        if (!defined('FTP_BINARY')) {
            $this->markTestSkipped('The FTP_BINARY constant is not defined');
        }

        $manager = $this->getManager();

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->driver(
                'ftp',
                [
                    'host'     => 'ftp.example.com',
                    'port'     => 21,
                    'username' => 'your-username',
                    'password' => 'your-password',
                ]
            )
        );
    }

    public function testGridFSConnectorDriver()
    {
        if (!class_exists(MongoClient::class) || !class_exists(Mongo::class)) {
            $this->markTestSkipped('The MongoClient class does not exist');
        }

        $manager = $this->getManager();

        try {
            $this->assertInstanceOf(
                FilesystemAdapter::class,
                $manager->driver(
                    'gridfs',
                    [
                        'server'   => 'mongodb://localhost:27017',
                        'database' => 'your-database',
                    ]
                )
            );
        } catch (MongoConnectionException $e) {
            $this->markTestSkipped('No mongo serer running');
        }
    }

    public function testLocalConnectorDriver()
    {
        $manager = $this->getManager();

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->driver(
                'local',
                [
                    'path' => __DIR__,
                ]
            )
        );
    }

    public function testNullConnectorDriver()
    {
        $manager = $this->getManager();

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->driver(
                'null'
            )
        );
    }

    public function testRackspaceConnectorDriver()
    {
        $manager = $this->getManager();

        try {
            $this->assertInstanceOf(
                FilesystemAdapter::class,
                $manager->driver(
                    'rackspace',
                    [
                        'endpoint'  => 'https://lon.identity.api.rackspacecloud.com/v2.0/',
                        'region'    => 'LON',
                        'username'  => 'your-username',
                        'apiKey'    => 'your-api-key',
                        'container' => 'your-container',
                    ]
                )
            );
        } catch (CurlException $e) {
            $this->markTestSkipped('No internet connection');
        } catch (ClientErrorResponseException $e) {
            $this->markTestSkipped('Client error response');
        }
    }

    public function testSftpConnectorDriver()
    {
        $manager = $this->getManager();

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->driver(
                'sftp',
                [
                    'host'     => 'sftp.example.com',
                    'port'     => 22,
                    'username' => 'your-username',
                    'password' => 'your-password',
                ]
            )
        );
    }

    public function testVfsConnectorDriver()
    {
        $manager = $this->getManager();

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->driver(
                'vfs'
            )
        );
    }

    public function testWebDavConnectorDriver()
    {
        $manager = $this->getManager();

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->driver(
                'webdav',
                [
                    'baseUri'  => 'http://example.org/dav/',
                    'userName' => 'your-username',
                    'password' => 'your-password',
                ]
            )
        );
    }

    public function testZipConnectorDriver()
    {
        $manager = $this->getManager();

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->driver(
                'zip',
                [
                    'path' => __DIR__ . '\Adapters\stubs\test.zip',
                ]
            )
        );
    }
}
