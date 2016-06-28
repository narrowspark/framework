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
        'class' => stdClass::class,
        'foo' => 'foo'
    ];

    protected function createTestConnection($config = null)
    {
        return true;
    }

    protected function createFooConnection($config = null)
    {
        return (new class() {
            public function getName(): string
            {
                return 'manager';
            }
        }
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function createConnection(array $config = [])
    {
        return $this->supportedConnectors[$config['name']];
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
