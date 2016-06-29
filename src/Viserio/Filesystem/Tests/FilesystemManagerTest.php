<?php
namespace Viserio\Filesystem\Tests;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\CurlException;
use MongoConnectionException;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\Config\Manager as ConfigManger;
use Viserio\Filesystem\{
    FilesystemAdapter,
    FilesystemManager
};
class FilesystemManagerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testAwsS3ConnectorDriver()
    {
        if (defined('HHVM_VERSION') && version_compare(HHVM_VERSION, '3.9.0') < 0) {
            $this->markTestSkipped('The AWS SDK requires a newer verison of HHVM');
        }

        $config = $this->mock(ConfigManger::class);
        $config->shouldReceive('get')
            ->once()
            ->with('filesystem.connections', [])
            ->andReturn([
                'awss3' => [
                    'key'     => 'your-key',
                    'secret'  => 'your-secret',
                    'bucket'  => 'your-bucket',
                    'region'  => 'us-east-1',
                    'version' => 'latest',
                ]
            ]);

        $manager = new FilesystemManager($config);

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->connection('awss3')
        );
    }

    public function testDropboxConnectorDriver()
    {
        $config = $this->mock(ConfigManger::class);
        $config->shouldReceive('get')
            ->once()
            ->with('filesystem.connections', [])
            ->andReturn([
                'dropbox' => [
                    'token' => 'your-token',
                    'app'   => 'your-app',
                ]
            ]);

        $manager = new FilesystemManager($config);

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->connection('dropbox')
        );
    }

    public function testFtpConnectorDriver()
    {
        if (! defined('FTP_BINARY')) {
            $this->markTestSkipped('The FTP_BINARY constant is not defined');
        }

        $config = $this->mock(ConfigManger::class);
        $config->shouldReceive('get')
            ->once()
            ->with('filesystem.connections', [])
            ->andReturn([
                'ftp' => [
                    'host'     => 'ftp.example.com',
                    'port'     => 21,
                    'username' => 'your-username',
                    'password' => 'your-password',
                ]
            ]);

        $manager = new FilesystemManager($config);

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->connection('ftp')
        );
    }

    public function testGridFSConnectorDriver()
    {
        if (! class_exists(MongoClient::class) || ! class_exists(Mongo::class)) {
            $this->markTestSkipped('The MongoClient class does not exist');
        }

        $config = $this->mock(ConfigManger::class);
        $config->shouldReceive('get')
            ->once()
            ->with('filesystem.connections', [])
            ->andReturn([
                'gridfs' => [
                    'server'   => 'mongodb://localhost:27017',
                    'database' => 'your-database',
                ]
            ]);

        $manager = new FilesystemManager($config);

        try {
            $this->assertInstanceOf(
                FilesystemAdapter::class,
                $manager->connection('gridfs')
            );
        } catch (MongoConnectionException $e) {
            $this->markTestSkipped('No mongo serer running');
        }
    }

    public function testLocalConnectorDriver()
    {
        $config = $this->mock(ConfigManger::class);
        $config->shouldReceive('get')
            ->once()
            ->with('filesystem.connections', [])
            ->andReturn([
                'local' => [
                    'path' => __DIR__,
                ]
            ]);

        $manager = new FilesystemManager($config);

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->connection('local')
        );
    }

    public function testNullConnectorDriver()
    {
        $config = $this->mock(ConfigManger::class);
        $config->shouldReceive('get')
            ->once()
            ->with('filesystem.connections', [])
            ->andReturn([
                'null' => []
            ]);

        $manager = new FilesystemManager($config);

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->connection('null')
        );
    }

    public function testRackspaceConnectorDriver()
    {
        $config = $this->mock(ConfigManger::class);
        $config->shouldReceive('get')
            ->once()
            ->with('filesystem.connections', [])
            ->andReturn([
                'rackspace' => [
                    'endpoint'  => 'https://lon.identity.api.rackspacecloud.com/v2.0/',
                    'region'    => 'LON',
                    'username'  => 'your-username',
                    'apiKey'    => 'your-api-key',
                    'container' => 'your-container',
                ]
            ]);

        $manager = new FilesystemManager($config);

        try {
            $this->assertInstanceOf(
                FilesystemAdapter::class,
                $manager->connection('rackspace')
            );
        } catch (CurlException $e) {
            $this->markTestSkipped('No internet connection');
        } catch (ClientErrorResponseException $e) {
            $this->markTestSkipped('Client error response');
        }
    }

    public function testSftpConnectorDriver()
    {
        $config = $this->mock(ConfigManger::class);
        $config->shouldReceive('get')
            ->once()
            ->with('filesystem.connections', [])
            ->andReturn([
                'sftp' => [
                    'host'     => 'sftp.example.com',
                    'port'     => 22,
                    'username' => 'your-username',
                    'password' => 'your-password',
                ]
            ]);

        $manager = new FilesystemManager($config);

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->connection('sftp')
        );
    }

    public function testVfsConnectorDriver()
    {
        $config = $this->mock(ConfigManger::class);
        $config->shouldReceive('get')
            ->once()
            ->with('filesystem.connections', [])
            ->andReturn([
                'vfs' => []
            ]);

        $manager = new FilesystemManager($config);

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->connection('vfs')
        );
    }

    public function testWebDavConnectorDriver()
    {
        $config = $this->mock(ConfigManger::class);
        $config->shouldReceive('get')
            ->once()
            ->with('filesystem.connections', [])
            ->andReturn([
                'webdav' => [
                    'baseUri'  => 'http://example.org/dav/',
                    'userName' => 'your-username',
                    'password' => 'your-password',
                ]
            ]);

        $manager = new FilesystemManager($config);

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->connection('webdav')
        );
    }

    public function testZipConnectorDriver()
    {
        $config = $this->mock(ConfigManger::class);
        $config->shouldReceive('get')
            ->once()
            ->with('filesystem.connections', [])
            ->andReturn([
                'zip' => [
                    'path' => __DIR__ . '\Adapters\stubs\test.zip',
                ]
            ]);

        $manager = new FilesystemManager($config);

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->connection('zip')
        );
    }
}
