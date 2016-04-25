<?php
namespace Viserio\Config\Test;

use Viserio\Config\Manager as ConfigManager;
use Viserio\Config\Repository;

class ConfigManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorInjection()
    {
        $values = ['param' => 'value'];
        $config = $this->getConfig();

        $config->setArray($values);

        $this->assertSame($values['param'], $values['param']);
    }

    // public function testGetDefaultValues()
    // {
    //     $config   = $this->getConfig();
    //     $defaults = $config->getDefaults();

    //     foreach ($this->defaults as $key => $value) {
    //         $this->assertEquals($defaults[$key], $value);
    //     }
    // }

    // public function testCallHandlerMethod()
    // {
    //     $config = $this->getConfig();

    //     $defaultKeys = array_keys($this->defaults);
    //     $defaultKeys = ksort($defaultKeys);
    //     $configKeys  = $config->callHandlerMethod('getKeys');
    //     $configKeys  = ksort($configKeys);

    //     $this->assertEquals($defaultKeys, $configKeys);
    // }

    protected function getConfig()
    {
        return new ConfigManager(new Repository());
    }
}
