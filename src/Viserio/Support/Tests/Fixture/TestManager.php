<?php
namespace Viserio\Support\Tests\Fixture;

use Viserio\Support\Manager;

class TestManager extends Manager
{
    protected $defaultDriver;

    protected $supportedDrivers = [
        'value',
        'test'        => 'test',
        'config'      => 'config',
        'throw'       => 'throw',
        'testmanager' => TestManager::class
    ];

    /**
     * Set the default cache driver name.
     *
     * @param string $name
     */
    public function setDefaultDriver($name)
    {
        $this->defaultDriver = $name;
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
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
}
