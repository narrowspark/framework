<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapters;

use League\Flysystem\AwsS3v3\AwsS3Adapter;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapters\AwsS3Connector;

class AwsS3ConnectorTest extends TestCase
{
    public function testConnectStandard()
    {
        $connector = new AwsS3Connector();

        $return = $connector->connect([
            'key'     => 'your-key',
            'secret'  => 'your-secret',
            'bucket'  => 'your-bucket',
            'region'  => 'us-east-1',
            'version' => 'latest',
        ]);

        self::assertInstanceOf(AwsS3Adapter::class, $return);
    }

    public function testConnectWithPrefix()
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
        self::assertInstanceOf(AwsS3Adapter::class, $return);
    }

    public function testConnectWithBucketEndPoint()
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

        self::assertInstanceOf(AwsS3Adapter::class, $return);
    }

    public function testConnectWithCalculateMD5()
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

        self::assertInstanceOf(AwsS3Adapter::class, $return);
    }

    public function testConnectWithScheme()
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

        self::assertInstanceOf(AwsS3Adapter::class, $return);
    }

    public function testConnectWithEndPoint()
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
        self::assertInstanceOf(AwsS3Adapter::class, $return);
    }

    public function testConnectWithEverything()
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

        self::assertInstanceOf(AwsS3Adapter::class, $return);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The awss3 connector requires a bucket configuration.
     */
    public function testConnectWithoutBucket()
    {
        $connector = new AwsS3Connector();

        $connector->connect([
            'key'     => 'your-key',
            'secret'  => 'your-secret',
            'region'  => 'us-east-1',
            'version' => 'latest',
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The awss3 connector requires authentication.
     */
    public function testConnectWithoutKey()
    {
        $connector = new AwsS3Connector();

        $connector->connect([
            'secret'  => 'your-secret',
            'bucket'  => 'your-bucket',
            'region'  => 'us-east-1',
            'version' => 'latest',
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The awss3 connector requires authentication.
     */
    public function testConnectWithoutSecret()
    {
        $connector = new AwsS3Connector();

        $connector->connect([
            'key'     => 'your-key',
            'bucket'  => 'your-bucket',
            'region'  => 'us-east-1',
            'version' => 'latest',
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The awss3 connector requires version configuration.
     */
    public function testConnectWithoutVersion()
    {
        $connector = new AwsS3Connector();

        $connector->connect([
            'key'    => 'your-key',
            'secret' => 'your-secret',
            'bucket' => 'your-bucket',
            'region' => 'us-east-1',
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The awss3 connector requires region configuration.
     */
    public function testConnectWithoutRegion()
    {
        $connector = new AwsS3Connector();

        $connector->connect([
            'key'     => 'your-key',
            'secret'  => 'your-secret',
            'bucket'  => 'your-bucket',
            'version' => 'latest',
        ]);
    }
}
