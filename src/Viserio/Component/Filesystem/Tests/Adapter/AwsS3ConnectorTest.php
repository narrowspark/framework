<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapter;

use League\Flysystem\AwsS3v3\AwsS3Adapter;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\AwsS3Connector;

/**
 * @internal
 */
final class AwsS3ConnectorTest extends TestCase
{
    public function testConnectStandard(): void
    {
        $connector = new AwsS3Connector();

        $return = $connector->connect([
            'key'     => 'your-key',
            'secret'  => 'your-secret',
            'bucket'  => 'your-bucket',
            'region'  => 'us-east-1',
            'version' => 'latest',
        ]);

        $this->assertInstanceOf(AwsS3Adapter::class, $return);
    }

    public function testConnectWithPrefix(): void
    {
        $connector = new AwsS3Connector();

        $return = $connector->connect([
            'key'     => 'your-key',
            'secret'  => 'your-secret',
            'bucket'  => 'your-bucket',
            'region'  => 'us-east-1',
            'version' => 'latest',
            'prefix'  => 'your-prefix',
        ]);
        $this->assertInstanceOf(AwsS3Adapter::class, $return);
    }

    public function testConnectWithBucketEndPoint(): void
    {
        $connector = new AwsS3Connector();

        $return = $connector->connect([
            'key'             => 'your-key',
            'secret'          => 'your-secret',
            'bucket'          => 'your-bucket',
            'region'          => 'us-east-1',
            'version'         => 'latest',
            'bucket_endpoint' => false,
        ]);

        $this->assertInstanceOf(AwsS3Adapter::class, $return);
    }

    public function testConnectWithCalculateMD5(): void
    {
        $connector = new AwsS3Connector();

        $return = $connector->connect([
            'key'           => 'your-key',
            'secret'        => 'your-secret',
            'bucket'        => 'your-bucket',
            'region'        => 'us-east-1',
            'version'       => 'latest',
            'calculate_md5' => true,
        ]);

        $this->assertInstanceOf(AwsS3Adapter::class, $return);
    }

    public function testConnectWithScheme(): void
    {
        $connector = new AwsS3Connector();

        $return = $connector->connect([
            'key'     => 'your-key',
            'secret'  => 'your-secret',
            'bucket'  => 'your-bucket',
            'region'  => 'us-east-1',
            'version' => 'latest',
            'scheme'  => 'https',
        ]);

        $this->assertInstanceOf(AwsS3Adapter::class, $return);
    }

    public function testConnectWithEndPoint(): void
    {
        $connector = new AwsS3Connector();

        $return = $connector->connect([
            'key'      => 'your-key',
            'secret'   => 'your-secret',
            'bucket'   => 'your-bucket',
            'region'   => 'us-east-1',
            'version'  => 'latest',
            'endpoint' => 'https://example.com',
        ]);
        $this->assertInstanceOf(AwsS3Adapter::class, $return);
    }

    public function testConnectWithEverything(): void
    {
        $connector = new AwsS3Connector();

        $return = $connector->connect([
            'key'             => 'your-key',
            'secret'          => 'your-secret',
            'bucket'          => 'your-bucket',
            'region'          => 'your-region',
            'version'         => 'latest',
            'bucket_endpoint' => false,
            'calculate_md5'   => true,
            'scheme'          => 'https',
            'endpoint'        => 'https://example.com',
        ]);

        $this->assertInstanceOf(AwsS3Adapter::class, $return);
    }

    public function testConnectWithoutBucket(): void
    {
        $this->expectException(\Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The awss3 connector requires a bucket configuration.');

        $connector = new AwsS3Connector();

        $connector->connect([
            'key'     => 'your-key',
            'secret'  => 'your-secret',
            'region'  => 'us-east-1',
            'version' => 'latest',
        ]);
    }

    public function testConnectWithoutKey(): void
    {
        $connector = new AwsS3Connector();

        $return = $connector->connect([
            'secret'  => 'your-secret',
            'bucket'  => 'your-bucket',
            'region'  => 'us-east-1',
            'version' => 'latest',
        ]);

        $this->assertInstanceOf(AwsS3Adapter::class, $return);
    }

    public function testConnectWithoutSecretButWithKey(): void
    {
        $this->expectException(\Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The awss3 connector requires authentication.');

        $connector = new AwsS3Connector();

        $connector->connect([
            'bucket'  => 'your-bucket',
            'region'  => 'us-east-1',
            'version' => 'latest',
            'key'     => 'dsdsadada',
        ]);
    }

    public function testConnectWithoutSecret(): void
    {
        $this->expectException(\Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The awss3 connector requires authentication.');

        $connector = new AwsS3Connector();

        $connector->connect([
            'key'     => 'your-key',
            'bucket'  => 'your-bucket',
            'region'  => 'us-east-1',
            'version' => 'latest',
        ]);
    }

    public function testConnectWithoutVersion(): void
    {
        $this->expectException(\Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The awss3 connector requires version configuration.');

        $connector = new AwsS3Connector();

        $connector->connect([
            'key'    => 'your-key',
            'secret' => 'your-secret',
            'bucket' => 'your-bucket',
            'region' => 'us-east-1',
        ]);
    }

    public function testConnectWithoutRegion(): void
    {
        $this->expectException(\Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The awss3 connector requires region configuration.');

        $connector = new AwsS3Connector();

        $connector->connect([
            'key'     => 'your-key',
            'secret'  => 'your-secret',
            'bucket'  => 'your-bucket',
            'version' => 'latest',
        ]);
    }
}
