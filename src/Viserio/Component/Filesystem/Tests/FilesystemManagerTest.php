<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Filesystem\Tests;

use League\Flysystem\AdapterInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use ParagonIE\Halite\KeyFactory;
use Viserio\Component\Filesystem\Encryption\EncryptionWrapper;
use Viserio\Component\Filesystem\FilesystemAdapter;
use Viserio\Component\Filesystem\FilesystemManager;
use Viserio\Contract\Cache\Manager as CacheManager;

/**
 * @internal
 *
 * @small
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
                            'key' => 'your-key',
                            'secret' => 'your-secret',
                            'bucket' => 'your-bucket',
                            'auth' => [
                                'region' => 'us-east-1',
                                'version' => 'latest',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        self::assertInstanceOf(
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

        self::assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('dropbox')
        );
    }

    public function testFtpConnectorDriver(): void
    {
        if (! \defined('FTP_BINARY')) {
            self::markTestSkipped('The FTP_BINARY constant is not defined');
        }

        $manager = new FilesystemManager([
            'viserio' => [
                'filesystem' => [
                    'connections' => [
                        'ftp' => [
                            'host' => 'ftp.example.com',
                            'port' => 21,
                            'username' => 'your-username',
                            'password' => 'your-password',
                        ],
                    ],
                ],
            ],
        ]);

        self::assertInstanceOf(
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

        self::assertInstanceOf(
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

        self::assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('null')
        );
    }

    public function testSftpConnectorDriver(): void
    {
        $manager = new FilesystemManager([
            'viserio' => [
                'filesystem' => [
                    'connections' => [
                        'sftp' => [
                            'host' => 'sftp.example.com',
                            'port' => 22,
                            'username' => 'your-username',
                            'password' => 'your-password',
                        ],
                    ],
                ],
            ],
        ]);

        self::assertInstanceOf(
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

        self::assertInstanceOf(
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
                                'baseUri' => 'http://example.org/dav/',
                            ],
                            'userName' => 'your-username',
                            'password' => 'your-password',
                        ],
                    ],
                ],
            ],
        ]);

        self::assertInstanceOf(
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

        self::assertInstanceOf(
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

        self::assertInstanceOf(
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
                            'path' => __DIR__,
                            'cache' => 'local',
                        ],
                    ],
                    'cached' => [
                        'local' => [
                            'driver' => 'local',
                            'key' => 'test',
                            'expire' => 6000,
                        ],
                    ],
                ],
            ],
        ]);

        $cacheManager = \Mockery::mock(CacheManager::class);
        $cacheManager->shouldReceive('hasDriver')
            ->once();

        $manager->setCacheManager($cacheManager);

        self::assertInstanceOf(
            FilesystemAdapter::class,
            $manager->getConnection('local')
        );
    }

    public function testGetCryptedConnection(): void
    {
        $key = KeyFactory::generateEncryptionKey();
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

        self::assertInstanceOf(
            EncryptionWrapper::class,
            $manager->cryptedConnection($key, 'local')
        );
    }
}
