<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapter;

use Guzzle\Http\Exception\CurlException;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\RackspaceConnector;

class RackspaceConnectorTest extends TestCase
{
    /**
     * @expectedException \Guzzle\Http\Exception\ClientErrorResponseException
     */
    public function testConnect(): void
    {
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
            $this->markTestSkipped('No internet connection');
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The rackspace connector requires endpoint configuration.
     */
    public function testConnectWithoutEndpoint(): void
    {
        $connector = new RackspaceConnector();

        $connector->connect([
            'region'    => 'LON',
            'username'  => 'your-username',
            'apiKey'    => 'your-api-key',
            'container' => null,
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The rackspace connector requires region configuration.
     */
    public function testConnectWithoutRegion(): void
    {
        $connector = new RackspaceConnector();

        $connector->connect([
            'endpoint'  => 'https://lon.identity.api.rackspacecloud.com/v2.0/',
            'username'  => 'your-username',
            'apiKey'    => 'your-api-key',
            'container' => null,
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The rackspace connector requires authentication.
     */
    public function testConnectWithoutUsername(): void
    {
        $connector = new RackspaceConnector();

        $connector->connect([
            'endpoint'  => 'https://lon.identity.api.rackspacecloud.com/v2.0/',
            'region'    => 'LON',
            'apiKey'    => 'your-api-key',
            'container' => null,
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The rackspace connector requires authentication.
     */
    public function testConnectWithoutApiKey(): void
    {
        $connector = new RackspaceConnector();

        $connector->connect([
            'endpoint'  => 'https://lon.identity.api.rackspacecloud.com/v2.0/',
            'region'    => 'LON',
            'username'  => 'your-username',
            'container' => null,
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The rackspace connector requires container configuration.
     */
    public function testConnectWithoutContainer(): void
    {
        $connector = new RackspaceConnector();

        $connector->connect([
            'endpoint' => 'https://lon.identity.api.rackspacecloud.com/v2.0/',
            'region'   => 'LON',
            'username' => 'your-username',
            'apiKey'   => 'your-api-key',
        ]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage [OpenCloud\ObjectStore\Service::getContainer] expects only \stdClass or null.
     */
    public function testConnectWithWrongContainer(): void
    {
        $connector = new RackspaceConnector();

        $connector->connect([
            'endpoint'  => 'https://lon.identity.api.rackspacecloud.com/v2.0/',
            'region'    => 'LON',
            'username'  => 'your-username',
            'apiKey'    => 'your-api-key',
            'container' => 'test',
        ]);
    }

    /**
     * @expectedException \Guzzle\Http\Exception\ClientErrorResponseException
     */
    public function testConnectWithInternal(): void
    {
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
            $this->markTestSkipped('No internet connection');
        }
    }

    /**
     * @expectedException \Guzzle\Http\Exception\ClientErrorResponseException
     */
    public function testConnectWithInternalFalse(): void
    {
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
            $this->markTestSkipped('No internet connection');
        }
    }
}
