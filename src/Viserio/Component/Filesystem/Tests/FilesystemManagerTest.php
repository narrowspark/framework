<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests;

use Defuse\Crypto\Key;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\CurlException;
use League\Flysystem\AdapterInterface;
use MongoClient;
use MongoConnectionException;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Cache\Manager as CacheManager;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Filesystem\Encryption\EncryptionWrapper;
use Viserio\Component\Filesystem\FilesystemAdapter;
use Viserio\Component\Filesystem\FilesystemManager;

class FilesystemManagerTest extends MockeryTestCase
{
    public function testAwsS3ConnectorDriver()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections'   => [
                        'awss3' => [
                            'key'     => 'your-key',
                            'secret'  => 'your-secret',
                            'bucket'  => 'your-bucket',
                            'region'  => 'us-east-1',
                            'version' => 'latest',
                        ],
                    ],
                ],
            ]);

        $manager = new FilesystemManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        self::assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('awss3')
        );
    }

    public function testDropboxConnectorDriver()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections'   => [
                        'dropbox' => [
                            'token' => 'your-token',
                            'app'   => 'your-app',
                        ],
                    ],
                ],
            ]);

        $manager = new FilesystemManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        self::assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('dropbox')
        );
    }

    public function testFtpConnectorDriver()
    {
        if (! defined('FTP_BINARY')) {
            $this->markTestSkipped('The FTP_BINARY constant is not defined');
        }

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections'   => [
                        'ftp' => [
                            'host'     => 'ftp.example.com',
                            'port'     => 21,
                            'username' => 'your-username',
                            'password' => 'your-password',
                        ],
                    ],
                ],
            ]);

        $manager = new FilesystemManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        self::assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('ftp')
        );
    }

    public function testGridFSConnectorDriver()
    {
        if (! class_exists(MongoClient::class)) {
            $this->markTestSkipped('The MongoClient class does not exist');
        }

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections'   => [
                        'gridfs' => [
                            'server'   => 'mongodb://localhost:27017',
                            'database' => 'your-database',
                        ],
                    ],
                ],
            ]);

        $manager = new FilesystemManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        try {
            self::assertInstanceOf(
                FilesystemAdapter::class,
                $manager->getConnection('gridfs')
            );
        } catch (MongoConnectionException $e) {
            $this->markTestSkipped('No mongo serer running');
        }
    }

    public function testLocalConnectorDriver()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections'   => [
                        'local' => [
                            'path' => __DIR__,
                        ],
                    ],
                ],
            ]);

        $manager = new FilesystemManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        self::assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('local')
        );
    }

    public function testNullConnectorDriver()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections'   => [
                        'null' => [],
                    ],
                ],
            ]);

        $manager = new FilesystemManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        self::assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('null')
        );
    }

    public function testRackspaceConnectorDriver()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections'   => [
                        'rackspace' => [
                            'endpoint'  => 'https://lon.identity.api.rackspacecloud.com/v2.0/',
                            'region'    => 'LON',
                            'username'  => 'your-username',
                            'apiKey'    => 'your-api-key',
                            'container' => 'your-container',
                        ],
                    ],
                ],
            ]);

        $manager = new FilesystemManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        try {
            self::assertInstanceOf(
                FilesystemAdapter::class,
                $manager->getConnection('rackspace')
            );
        } catch (CurlException $e) {
            $this->markTestSkipped('No internet connection');
        } catch (ClientErrorResponseException $e) {
            $this->markTestSkipped('Client error response');
        }
    }

    public function testSftpConnectorDriver()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections'   => [
                        'sftp' => [
                            'host'     => 'sftp.example.com',
                            'port'     => 22,
                            'username' => 'your-username',
                            'password' => 'your-password',
                        ],
                    ],
                ],
            ]);

        $manager = new FilesystemManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        self::assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('sftp')
        );
    }

    public function testVfsConnectorDriver()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections'   => [
                        'vfs' => [],
                    ],
                ],
            ]);

        $manager = new FilesystemManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        self::assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('vfs')
        );
    }

    public function testWebDavConnectorDriver()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections'   => [
                        'webdav' => [
                            'baseUri'  => 'http://example.org/dav/',
                            'userName' => 'your-username',
                            'password' => 'your-password',
                        ],
                    ],
                ],
            ]);

        $manager = new FilesystemManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        self::assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('webdav')
        );
    }

    public function testZipConnectorDriver()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections'   => [
                        'zip' => [
                            'path' => __DIR__ . '\Adapters\stubs\test.zip',
                        ],
                    ],
                ],
            ]);

        $manager = new FilesystemManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        self::assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('zip')
        );
    }

    public function testgetFlysystemAdapter()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections'   => [
                        'zip' => [
                            'path' => __DIR__ . '\Adapters\stubs\test.zip',
                        ],
                    ],
                ],
            ]);

        $manager = new FilesystemManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        self::assertInstanceOf(
            AdapterInterface::class,
            $manager->getFlysystemAdapter('zip')
        );
    }

    public function testCachedAdapter()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections'   => [
                        'local' => [
                            'path'  => __DIR__,
                            'cache' => 'local',
                        ],
                    ],
                    'cached' => [
                        'local' => [
                            'driver' => 'local',
                            'key'    => 'test',
                            'expire' => 6000,
                        ],
                    ],
                ],
            ]);

        $manager = new FilesystemManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        $cacheManager = $this->mock(CacheManager::class);
        $cacheManager->shouldReceive('hasDriver')
            ->once();

        $manager->setCacheManager($cacheManager);

        self::assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('local')
        );
    }

    public function testGetCryptedConnection()
    {
        $key    = Key::createNewRandomKey();
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections'   => [
                        'local' => [
                            'path' => __DIR__,
                        ],
                    ],
                ],
            ]);

        $manager = new FilesystemManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        self::assertInstanceOf(
            EncryptionWrapper::class,
            $manager->cryptedConnection($key, 'local')
        );
    }
}
