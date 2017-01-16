<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapters;

use League\Flysystem\GridFS\GridFSAdapter;
use MongoClient;
use MongoConnectionException;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapters\GridFSConnector;

class GridFSConnectorTest extends TestCase
{
    public function testConnectStandard()
    {
        if (! class_exists(MongoClient::class)) {
            $this->markTestSkipped('The MongoClient class does not exist');
        }

        $connector = new GridFSConnector();

        try {
            $return = $connector->connect([
                'server'   => 'mongodb://localhost:27017',
                'database' => 'your-database',
            ]);
            self::assertInstanceOf(GridFSAdapter::class, $return);
        } catch (MongoConnectionException $e) {
            $this->markTestSkipped('No mongo serer running');
        }
    }

    /**
     * @depends testConnectStandard
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The gridfs connector requires database configuration.
     */
    public function testConnectWithoutDatabase()
    {
        $connector = new GridFSConnector();

        $connector->connect(['server' => 'mongodb://localhost:27017']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The gridfs connector requires server configuration.
     */
    public function testConnectWithoutServer()
    {
        $connector = new GridFSConnector();

        $connector->connect(['database' => 'your-database']);
    }
}
