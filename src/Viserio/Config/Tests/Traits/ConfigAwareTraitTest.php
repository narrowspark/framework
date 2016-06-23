<?php
namespace Viserio\Config\Tests\Traits;

use Viserio\Config\Manager as ConfigManager;
use Viserio\Config\Repository;
use Viserio\Config\Traits\ConfigAwareTrait;

class ConfigAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    use ConfigAwareTrait;

    public function testSetAndGetConfig()
    {
        $this->setConfig(new ConfigManager(new Repository()));

        $this->assertInstanceOf(ConfigManager::class, $this->getConfig());
    }
}
