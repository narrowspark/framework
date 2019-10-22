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

namespace Viserio\Component\Filesystem\Tests\Adapter;

use League\Flysystem\AwsS3v3\AwsS3Adapter;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\AwsS3Connector;

/**
 * @internal
 *
 * @small
 */
final class AwsS3ConnectorTest extends TestCase
{
    public function testConnectStandard(): void
    {
        $connector = new AwsS3Connector([
            'key' => 'your-key',
            'secret' => 'your-secret',
            'bucket' => 'your-bucket',
            'auth' => [
                'region' => 'us-east-1',
                'version' => 'latest',
            ],
        ]);

        $return = $connector->connect();

        self::assertInstanceOf(AwsS3Adapter::class, $return);
    }

    public function testConnectWithPrefix(): void
    {
        $connector = new AwsS3Connector([
            'key' => 'your-key',
            'secret' => 'your-secret',
            'bucket' => 'your-bucket',
            'auth' => [
                'region' => 'us-east-1',
                'version' => 'latest',
            ],
            'prefix' => 'your-prefix',
        ]);

        $return = $connector->connect();

        self::assertInstanceOf(AwsS3Adapter::class, $return);
    }

    public function testConnectWithBucketEndPoint(): void
    {
        $connector = new AwsS3Connector([
            'key' => 'your-key',
            'secret' => 'your-secret',
            'bucket' => 'your-bucket',
            'auth' => [
                'region' => 'us-east-1',
                'version' => 'latest',
            ],
            'bucket_endpoint' => false,
        ]);

        $return = $connector->connect();

        self::assertInstanceOf(AwsS3Adapter::class, $return);
    }

    public function testConnectWithCalculateMD5(): void
    {
        $connector = new AwsS3Connector([
            'key' => 'your-key',
            'secret' => 'your-secret',
            'bucket' => 'your-bucket',
            'auth' => [
                'region' => 'us-east-1',
                'version' => 'latest',
            ],
            'calculate_md5' => true,
        ]);

        $return = $connector->connect();

        self::assertInstanceOf(AwsS3Adapter::class, $return);
    }

    public function testConnectWithScheme(): void
    {
        $connector = new AwsS3Connector([
            'key' => 'your-key',
            'secret' => 'your-secret',
            'bucket' => 'your-bucket',
            'auth' => [
                'region' => 'us-east-1',
                'version' => 'latest',
            ],
            'scheme' => 'https',
        ]);

        $return = $connector->connect();

        self::assertInstanceOf(AwsS3Adapter::class, $return);
    }

    public function testConnectWithEndPoint(): void
    {
        $connector = new AwsS3Connector([
            'key' => 'your-key',
            'secret' => 'your-secret',
            'bucket' => 'your-bucket',
            'auth' => [
                'region' => 'us-east-1',
                'version' => 'latest',
            ],
            'endpoint' => 'https://example.com',
        ]);

        $return = $connector->connect();

        self::assertInstanceOf(AwsS3Adapter::class, $return);
    }

    public function testConnectWithEverything(): void
    {
        $connector = new AwsS3Connector([
            'key' => 'your-key',
            'secret' => 'your-secret',
            'bucket' => 'your-bucket',
            'auth' => [
                'region' => 'your-region',
                'version' => 'latest',
            ],
            'bucket_endpoint' => false,
            'calculate_md5' => true,
            'scheme' => 'https',
            'endpoint' => 'https://example.com',
        ]);

        $return = $connector->connect();

        self::assertInstanceOf(AwsS3Adapter::class, $return);
    }

    public function testConnectWithoutKey(): void
    {
        $connector = new AwsS3Connector([
            'secret' => 'your-secret',
            'bucket' => 'your-bucket',
            'auth' => [
                'region' => 'us-east-1',
                'version' => 'latest',
            ],
        ]);

        $return = $connector->connect();

        self::assertInstanceOf(AwsS3Adapter::class, $return);
    }
}
