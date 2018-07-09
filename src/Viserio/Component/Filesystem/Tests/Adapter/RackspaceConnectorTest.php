<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapter;

use Guzzle\Http\Exception\CurlException;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\RackspaceConnector;

/**
 * @internal
 */
final class RackspaceConnectorTest extends TestCase
{
    public function testConnect(): void
    {
        $this->expectException(\Guzzle\Http\Exception\ClientErrorResponseException::class);

        $connector = new RackspaceConnector();

        try {
            $connector->connect([
                'endpoint'  => 'https://lon.identity.api.rackspacecloud.com/v2.0/',
                'region'    => 'LON',
                'username'  => 'your-username',
                'apiKey'    => 'your-api-key',
                'container' => null,
            ]);
        } catch (CurlException $e) {
            static::markTestSkipped('No internet connection');
        }
    }

    public function testConnectWithoutEndpoint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The rackspace connector requires endpoint configuration.');

        $connector = new RackspaceConnector();

        $connector->connect([
            'region'    => 'LON',
            'username'  => 'your-username',
            'apiKey'    => 'your-api-key',
            'container' => null,
        ]);
    }

    public function testConnectWithoutRegion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The rackspace connector requires region configuration.');

        $connector = new RackspaceConnector();

        $connector->connect([
            'endpoint'  => 'https://lon.identity.api.rackspacecloud.com/v2.0/',
            'username'  => 'your-username',
            'apiKey'    => 'your-api-key',
            'container' => null,
        ]);
    }

    public function testConnectWithoutUsername(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The rackspace connector requires authentication.');

        $connector = new RackspaceConnector();

        $connector->connect([
            'endpoint'  => 'https://lon.identity.api.rackspacecloud.com/v2.0/',
            'region'    => 'LON',
            'apiKey'    => 'your-api-key',
            'container' => null,
        ]);
    }

    public function testConnectWithoutApiKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The rackspace connector requires authentication.');

        $connector = new RackspaceConnector();

        $connector->connect([
            'endpoint'  => 'https://lon.identity.api.rackspacecloud.com/v2.0/',
            'region'    => 'LON',
            'username'  => 'your-username',
            'container' => null,
        ]);
    }

    public function testConnectWithoutContainer(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The rackspace connector requires container configuration.');

        $connector = new RackspaceConnector();

        $connector->connect([
            'endpoint' => 'https://lon.identity.api.rackspacecloud.com/v2.0/',
            'region'   => 'LON',
            'username' => 'your-username',
            'apiKey'   => 'your-api-key',
        ]);
    }

    public function testConnectWithWrongContainer(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('[OpenCloud\\ObjectStore\\Service::getContainer] expects only \\stdClass or null.');

        $connector = new RackspaceConnector();

        $connector->connect([
            'endpoint'  => 'https://lon.identity.api.rackspacecloud.com/v2.0/',
            'region'    => 'LON',
            'username'  => 'your-username',
            'apiKey'    => 'your-api-key',
            'container' => 'test',
        ]);
    }

    public function testConnectWithInternal(): void
    {
        $this->expectException(\Guzzle\Http\Exception\ClientErrorResponseException::class);

        $connector = new RackspaceConnector();

        try {
            $connector->connect([
                'endpoint'  => 'https://lon.identity.api.rackspacecloud.com/v2.0/',
                'region'    => 'LON',
                'username'  => 'your-username',
                'apiKey'    => 'your-api-key',
                'container' => null,
                'internal'  => true,
            ]);
        } catch (CurlException $e) {
            static::markTestSkipped('No internet connection');
        }
    }

    public function testConnectWithInternalFalse(): void
    {
        $this->expectException(\Guzzle\Http\Exception\ClientErrorResponseException::class);

        $connector = new RackspaceConnector();

        try {
            $connector->connect([
                'endpoint'  => 'https://lon.identity.api.rackspacecloud.com/v2.0/',
                'region'    => 'LON',
                'username'  => 'your-username',
                'apiKey'    => 'your-api-key',
                'container' => null,
                'internal'  => false,
            ]);
        } catch (CurlException $e) {
            static::markTestSkipped('No internet connection');
        }
    }
}
