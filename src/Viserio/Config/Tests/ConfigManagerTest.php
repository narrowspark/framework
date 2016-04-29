<?php
namespace Viserio\Config\Test;

use Viserio\Config\Manager as ConfigManager;
use Viserio\Config\Repository;
use Viserio\Config\FileLoader;
use Viserio\Filesystem\Filesystem;
use Viserio\Parsers\TaggableParser;

class ConfigManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorInjection()
    {
        $values = ['param' => 'value'];
        $config = new ConfigManager(new Repository());

        $config->setArray($values);

        $this->assertSame($values['param'], $values['param']);
    }

    public function testGetAndSetLoader()
    {
        $config = new ConfigManager(new Repository());

        $config->setLoader(new FileLoader(new TaggableParser(new Filesystem()), []));

        $this->assertInstanceOf(FileLoader::class, $config->getLoader());
    }

    protected function getConfig()
    {
        return new ConfigManager(new Repository());
    }
}
