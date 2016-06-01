<?php
namespace Viserio\Connect\Tests\Adapters;

use Viserio\Connect\Adapters\PredisConnector;

class PredisConnectorTest extends \PHPUnit_Framework_TestCase
{
    public function testConnect()
    {
        $connector = new PredisConnector();
        $config = [
            'servers' => [
                'server' => 'narrowspark',
            ],
            'options' => [
                'server' => 'narrowspark',
            ],
        ];

        $this->assertInstanceOf('Predis\Client', $connector->connect($config));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage servers config don't exist.
     */
    public function testConnectThrowExeption()
    {
        $connector = new PredisConnector();
        $config = [];

        $this->assertInstanceOf('Predis\Client', $connector->connect($config));
    }
}
