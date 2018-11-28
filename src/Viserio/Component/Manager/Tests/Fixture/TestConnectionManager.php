<?php
declare(strict_types=1);
namespace Viserio\Component\Manager\Tests\Fixture;

use Viserio\Component\Manager\AbstractConnectionManager;

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
    protected static function getConfigName(): string
    {
        return 'connection';
    }
}
