<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Extension;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Bridge\Twig\Extension\ConfigExtension;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;

class ConfigExtensionTest extends MockeryTestCase
{
    public function testGetFunctions()
    {
        $config = $this->mock(RepositoryContract::class);

        $extension = new ConfigExtension($config);
        $functions = $extension->getFunctions();

        self::assertEquals('config', $functions[0]->getName());
        self::assertEquals('get', $functions[0]->getCallable()[1]);

        self::assertEquals('config_get', $functions[1]->getName());
        self::assertEquals('get', $functions[1]->getCallable()[1]);

        self::assertEquals('config_has', $functions[2]->getName());
        self::assertEquals('has', $functions[2]->getCallable()[1]);
    }

    public function testGetName()
    {
        self::assertEquals(
            'Viserio_Bridge_Twig_Extension_Config',
            (new ConfigExtension($this->mock(RepositoryContract::class)))->getName()
        );
    }
}
