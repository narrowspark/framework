<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\CurlException;
use League\Flysystem\AdapterInterface;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use ParagonIE\Halite\KeyFactory;
use Viserio\Component\Contract\Cache\Manager as CacheManager;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Filesystem\Encryption\EncryptionWrapper;
use Viserio\Component\Filesystem\FilesystemAdapter;
use Viserio\Component\Filesystem\FilesystemManager;

/**
 * @internal
 */
final class FilesystemManagerTest extends MockeryTestCase
{
    public function testAwsS3ConnectorDriver(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $this->arrangeConfigOffsetExists($config);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections' => [
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

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('awss3')
        );
    }

    public function testDropboxConnectorDriver(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $this->arrangeConfigOffsetExists($config);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections' => [
                        'dropbox' => [
                            'token' => 'your-token',
                        ],
                    ],
                ],
            ]);

        $manager = new FilesystemManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('dropbox')
        );
    }

    public function testFtpConnectorDriver(): void
    {
        if (! \defined('FTP_BINARY')) {
            $this->markTestSkipped('The FTP_BINARY constant is not defined');
        }

        $config = $this->mock(RepositoryContract::class);
        $this->arrangeConfigOffsetExists($config);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections' => [
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

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('ftp')
        );
    }

    public function testLocalConnectorDriver(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $this->arrangeConfigOffsetExists($config);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections' => [
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

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('local')
        );
    }

    public function testNullConnectorDriver(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $this->arrangeConfigOffsetExists($config);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections' => [
                        'null' => [],
                    ],
                ],
            ]);

        $manager = new FilesystemManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('null')
        );
    }

    public function testRackspaceConnectorDriver(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $this->arrangeConfigOffsetExists($config);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections' => [
                        'rackspace' => [
                            'endpoint'  => 'https://lon.identity.api.rackspacecloud.com/v2.0/',
                            'region'    => 'LON',
                            'username'  => 'your-username',
                            'apiKey'    => 'your-api-key',
                            'container' => null,
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
            $this->assertInstanceOf(
                FilesystemAdapter::class,
                $manager->getConnection('rackspace')
            );
        } catch (CurlException $e) {
            $this->markTestSkipped('No internet connection');
        } catch (ClientErrorResponseException $e) {
            $this->markTestSkipped('Client error response');
        }
    }

    public function testSftpConnectorDriver(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $this->arrangeConfigOffsetExists($config);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections' => [
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

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('sftp')
        );
    }

    public function testVfsConnectorDriver(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $this->arrangeConfigOffsetExists($config);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections' => [
                        'vfs' => [],
                    ],
                ],
            ]);

        $manager = new FilesystemManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('vfs')
        );
    }

    public function testWebDavConnectorDriver(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $this->arrangeConfigOffsetExists($config);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections' => [
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

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('webdav')
        );
    }

    public function testZipConnectorDriver(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $this->arrangeConfigOffsetExists($config);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections' => [
                        'zip' => [
                            'path' => __DIR__ . '\Adapter\stubs\test.zip',
                        ],
                    ],
                ],
            ]);

        $manager = new FilesystemManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('zip')
        );
    }

    public function testgetFlysystemAdapter(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $this->arrangeConfigOffsetExists($config);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections' => [
                        'zip' => [
                            'path' => __DIR__ . '\Adapter\stubs\test.zip',
                        ],
                    ],
                ],
            ]);

        $manager = new FilesystemManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        $this->assertInstanceOf(
            AdapterInterface::class,
            $manager->getFlysystemAdapter('zip')
        );
    }

    public function testCachedAdapter(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $this->arrangeConfigOffsetExists($config);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections' => [
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

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('local')
        );
    }

    public function testGetCryptedConnection(): void
    {
        $key      = KeyFactory::generateEncryptionKey();
        $config   = $this->mock(RepositoryContract::class);
        $this->arrangeConfigOffsetExists($config);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'filesystem' => [
                    'connections' => [
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

        $this->assertInstanceOf(
            EncryptionWrapper::class,
            $manager->cryptedConnection($key, 'local')
        );
    }

    /**
     * @param \Mockery\MockInterface $config
     */
    private function arrangeConfigOffsetExists($config): void
    {
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
    }
}
