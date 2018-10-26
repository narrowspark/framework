<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\CurlException;
use League\Flysystem\AdapterInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use ParagonIE\Halite\KeyFactory;
use Viserio\Component\Contract\Cache\Manager as CacheManager;
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
        $manager = new FilesystemManager([
            'viserio' => [
                'filesystem' => [
                    'connections' => [
                        'awss3' => [
                            'key'     => 'your-key',
                            'secret'  => 'your-secret',
                            'bucket'  => 'your-bucket',
                            'auth'    => [
                                'region'  => 'us-east-1',
                                'version' => 'latest',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('awss3')
        );
    }

    public function testDropboxConnectorDriver(): void
    {
        $manager = new FilesystemManager([
            'viserio' => [
                'filesystem' => [
                    'connections' => [
                        'dropbox' => [
                            'token' => 'your-token',
                        ],
                    ],
                ],
            ],
        ]);

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

        $manager = new FilesystemManager([
            'viserio' => [
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
            ],
        ]);

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('ftp')
        );
    }

    public function testLocalConnectorDriver(): void
    {
        $manager = new FilesystemManager([
            'viserio' => [
                'filesystem' => [
                    'connections' => [
                        'local' => [
                            'path' => __DIR__,
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('local')
        );
    }

    public function testNullConnectorDriver(): void
    {
        $manager = new FilesystemManager([
            'viserio' => [
                'filesystem' => [
                    'connections' => [
                        'null' => [],
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('null')
        );
    }

    public function testRackspaceConnectorDriver(): void
    {
        $manager = new FilesystemManager([
            'viserio' => [
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
            ],
        ]);

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
        $manager = new FilesystemManager([
            'viserio' => [
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
            ],
        ]);

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('sftp')
        );
    }

    public function testVfsConnectorDriver(): void
    {
        $manager = new FilesystemManager([
            'viserio' => [
                'filesystem' => [
                    'connections' => [
                        'vfs' => [],
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('vfs')
        );
    }

    public function testWebDavConnectorDriver(): void
    {
        $manager = new FilesystemManager([
            'viserio' => [
                'filesystem' => [
                    'connections' => [
                        'webdav' => [
                            'auth' => [
                                'baseUri'  => 'http://example.org/dav/',
                            ],
                            'userName' => 'your-username',
                            'password' => 'your-password',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('webdav')
        );
    }

    public function testZipConnectorDriver(): void
    {
        $manager = new FilesystemManager([
            'viserio' => [
                'filesystem' => [
                    'connections' => [
                        'zip' => [
                            'path' => __DIR__ . \DIRECTORY_SEPARATOR . 'Adapter' . \DIRECTORY_SEPARATOR . 'stubs' . \DIRECTORY_SEPARATOR . 'test.zip',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('zip')
        );
    }

    public function testgetFlysystemAdapter(): void
    {
        $manager = new FilesystemManager([
            'viserio' => [
                'filesystem' => [
                    'connections' => [
                        'zip' => [
                            'path' => __DIR__ . \DIRECTORY_SEPARATOR . 'Adapter' . \DIRECTORY_SEPARATOR . 'stubs' . \DIRECTORY_SEPARATOR . 'test.zip',
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(
            AdapterInterface::class,
            $manager->getFlysystemAdapter('zip')
        );
    }

    public function testCachedAdapter(): void
    {
        $manager = new FilesystemManager([
            'viserio' => [
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
            ],
        ]);

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
        $key     = KeyFactory::generateEncryptionKey();
        $manager = new FilesystemManager([
            'viserio' => [
                'filesystem' => [
                    'connections' => [
                        'local' => [
                            'path' => __DIR__,
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertInstanceOf(
            EncryptionWrapper::class,
            $manager->cryptedConnection($key, 'local')
        );
    }
}
