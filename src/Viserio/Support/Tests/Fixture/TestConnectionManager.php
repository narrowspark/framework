<?php
declare(strict_types=1);
namespace Viserio\Support\Tests\Fixture;

use Viserio\Support\AbstractConnectionManager;

class TestConnectionManager extends AbstractConnectionManager
{
    protected function createTestConnection($config = null)
    {
        return true;
    }

    protected function createFooConnection($config = null)
    {
        return new class() {
            public function getName(): string
            {
                return 'manager';
            }
        };
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
