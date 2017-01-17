<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests\Fixture;

use stdClass;
use Viserio\Component\Support\AbstractManager;

class TestManager extends AbstractManager
{
    protected function createTestDriver($config = null)
    {
        return true;
    }

    protected function createConfigDriver($config)
    {
        return $config;
    }

    protected function createValueDriver($config)
    {
        return $config;
    }

    protected function createTestmanagerDriver($config)
    {
        return new stdClass();
    }

    /**
     * Get the configuration name.
     *
     * @return string
     */
    protected function getConfigName(): string
    {
        return 'test';
    }
}
