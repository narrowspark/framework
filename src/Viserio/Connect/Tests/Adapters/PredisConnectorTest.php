<?php

declare(strict_types=1);
namespace Viserio\Connect\Tests\Adapters;

use Predis\Client;
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

        $this->assertInstanceOf(Client::class, $connector->connect($config));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage servers config don't exist.
     */
    public function testConnectThrowExeption()
    {
        $connector = new PredisConnector();
        $config = [];

        $this->assertInstanceOf(Client::class, $connector->connect($config));
    }
}
