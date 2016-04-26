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

    protected function getConfig()
    {
        return new ConfigManager(new Repository());
    }
}
