<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Tests\Adapters;

use League\Flysystem\GridFS\GridFSAdapter;
use MongoClient;
use MongoConnectionException;
use MongoDB\Driver\Manager as MongoDBManager;
use Viserio\Filesystem\Adapters\GridFSConnector;

class GridFSConnectorTest extends \PHPUnit_Framework_TestCase
{
    public function testConnectStandard()
    {
        if (! class_exists(MongoClient::class)) {
            $this->markTestSkipped('The MongoClient class does not exist');
        }

        $connector = new GridFSConnector();

        try {
            $return = $connector->connect([
                'server' => 'mongodb://localhost:27017',
                'database' => 'your-database',
            ]);
            $this->assertInstanceOf(GridFSAdapter::class, $return);
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
