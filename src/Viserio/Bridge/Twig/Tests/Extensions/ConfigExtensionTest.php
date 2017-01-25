<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Extensions;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Twig\Extensions\ConfigExtension;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;

class ConfigExtensionTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testGetFunctions()
    {
        $extension = new ConfigExtension($this->mock(RepositoryContract::class));
        $functions = $extension->getFunctions();

        $this->assertEquals('config', $functions[0]->getName());
        $this->assertEquals('get', $functions[0]->getCallable()[1]);

        $this->assertEquals('config_get', $functions[1]->getName());
        $this->assertEquals('get', $functions[1]->getCallable()[1]);

        $this->assertEquals('config_has', $functions[2]->getName());
        $this->assertEquals('has', $functions[2]->getCallable()[1]);
    }

    public function testGetName()
    {
        $this->assertEquals(
            'Viserio_Bridge_Twig_Extension_Config',
            (new ConfigExtension($this->mock(RepositoryContract::class)))->getName()
        );
    }
}
