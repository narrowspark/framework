<?php
namespace Viserio\Support\Tests\Fixture;

use stdClass;
use Viserio\Support\AbstractConnectionManager;

class TestConnectionManager extends AbstractConnectionManager
{
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
     * Get the configuration name.
     *
     * @return string
     */
    protected function getConfigName(): string
    {
        return 'connection';
    }
}
