<?php
namespace Viserio\Support\Tests\Fixture;

use stdClass;
use Viserio\Support\AbstractManager;

class TestManager extends AbstractManager
{
    protected $defaultDriver;

    protected $supportedDrivers = [
        'value',
        'test'        => 'test',
        'config'      => 'config',
        'throw'       => 'throw',
        'testmanager' => stdClass::class,
    ];

    public function setDefaultDriver(string $name)
    {
        $this->defaultDriver = $name;

        return $this;
    }

    public function getDefaultDriver(): string
    {
        return $this->defaultDriver;
    }

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
