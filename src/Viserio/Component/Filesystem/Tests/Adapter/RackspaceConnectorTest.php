<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapter;

use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Http\Exception\CurlException;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Contract\OptionsResolver\Exception\InvalidArgumentException;
use Viserio\Component\Filesystem\Adapter\RackspaceConnector;

/**
 * @internal
 */
final class RackspaceConnectorTest extends TestCase
{
    public function testConnect(): void
    {
        $this->expectException(ClientErrorResponseException::class);

        $connector = new RackspaceConnector([
            'endpoint'  => 'https://lon.identity.api.rackspacecloud.com/v2.0/',
            'region'    => 'LON',
            'username'  => 'your-username',
            'apiKey'    => 'your-api-key',
            'container' => null,
        ]);

        try {
            $connector->connect();
        } catch (CurlException $e) {
            static::markTestSkipped('No internet connection');
        }
    }

    public function testConnectWithWrongContainer(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('[OpenCloud\\ObjectStore\\Service::getContainer] expects only \\stdClass or null.');

        $connector = new RackspaceConnector([
            'endpoint'  => 'https://lon.identity.api.rackspacecloud.com/v2.0/',
            'region'    => 'LON',
            'username'  => 'your-username',
            'apiKey'    => 'your-api-key',
            'container' => 'test',
        ]);

        $connector->connect();
    }
}
