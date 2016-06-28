<?php
namespace Viserio\Support\Tests\Fixture;

use stdClass;
use Viserio\Support\AbstractConnectionManager;

class TestConnectionManager extends AbstractConnectionManager
{
    /**
     * All supported connectors.
     *
     * @var array
     */
    protected $supportedConnectors = [
        'test' => 'test',
        'class' => stdClass::class
    ];

    protected function createTestConnection($config = null)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function createConnection(array $config = [])
    {

    }

    /**
     * Get the configuration name.
     *
     * @return string
     */
    protected function getConfigName(): string
    {
        return 'connection';
    }
}
