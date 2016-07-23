<?php

declare(strict_types=1);
namespace Viserio\Connect\Tests\Adapters\Database;

use PDO;
use Viserio\Connect\Tests\Fixture\DatabaseConnector;

class AbstractDatabaseConnectorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (! class_exists('PDO')) {
            $this->markTestSkipped('PDO module is not installed.');
        }
    }

    public function testGetDefaultOption()
    {
        $connector = new DatabaseConnector();
        $options = [
            PDO::ATTR_CASE              => PDO::CASE_NATURAL,
            PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
            PDO::ATTR_STRINGIFY_FETCHES => false,
            PDO::ATTR_EMULATE_PREPARES  => false,
        ];

        $this->assertTrue(is_array($connector->getDefaultOptions()));
        $this->assertSame($options, $connector->getDefaultOptions());
    }

    public function testSetDefaultOption()
    {
        $options = [
            PDO::ATTR_EMULATE_PREPARES  => true,
        ];

        $connector = new DatabaseConnector();
        $connector->setDefaultOptions($options);

        $this->assertTrue(is_array($connector->getDefaultOptions()));
        $this->assertSame($options, $connector->getDefaultOptions());
    }

    public function testGetOptions()
    {
        $connector = new DatabaseConnector();
        $config = [
            'options' => [
                PDO::ATTR_EMULATE_PREPARES  => true,
            ],
        ];

        $this->assertTrue(is_array($connector->getOptions($config)));
    }

    /**
     * @expectedException \PDOException
     */
    public function testCreateConnectionToThrowException()
    {
        $connector = new DatabaseConnector();
        $config = [
            'username' => '',
            'password' => '',
        ];

        $connector->createConnection('mysql:dbname=narrowspark;host=fails', $config, []);
    }
}
