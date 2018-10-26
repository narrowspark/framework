<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Extension;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Bridge\Twig\Extension\ConfigExtension;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;

/**
 * @internal
 */
final class ConfigExtensionTest extends MockeryTestCase
{
    public function testGetFunctions(): void
    {
        $config = $this->mock(RepositoryContract::class);

        $extension = new ConfigExtension($config);
        $functions = $extension->getFunctions();

        $this->assertEquals('config', $functions[0]->getName());
        $this->assertEquals('get', $functions[0]->getCallable()[1]);

        $this->assertEquals('config_get', $functions[1]->getName());
        $this->assertEquals('get', $functions[1]->getCallable()[1]);

        $this->assertEquals('config_has', $functions[2]->getName());
        $this->assertEquals('has', $functions[2]->getCallable()[1]);
    }

    public function testGetName(): void
    {
        $this->assertEquals(
            'Viserio_Bridge_Twig_Extension_Config',
            (new ConfigExtension($this->mock(RepositoryContract::class)))->getName()
        );
    }
}
