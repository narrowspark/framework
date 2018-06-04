<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Extension;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Bridge\Twig\Extension\SessionExtension;
use Viserio\Component\Contract\Session\Store as StoreContract;

/**
 * @internal
 */
final class SessionExtensionTest extends MockeryTestCase
{
    public function testGetFunctions(): void
    {
        $extension = new SessionExtension($this->mock(StoreContract::class));
        $functions = $extension->getFunctions();

        $this->assertEquals('session', $functions[0]->getName());
        $this->assertEquals('get', $functions[0]->getCallable()[1]);

        $this->assertEquals('csrf_token', $functions[1]->getName());
        $this->assertEquals('getToken', $functions[1]->getCallable()[1]);

        $this->assertEquals('csrf_field', $functions[2]->getName());
        $this->assertEquals('getCsrfField', $functions[2]->getCallable()[1]);

        $this->assertEquals('session_get', $functions[3]->getName());
        $this->assertEquals('get', $functions[3]->getCallable()[1]);

        $this->assertEquals('session_has', $functions[4]->getName());
        $this->assertEquals('has', $functions[4]->getCallable()[1]);
    }

    public function testGetName(): void
    {
        $this->assertEquals(
            'Viserio_Bridge_Twig_Extension_Session',
            (new SessionExtension($this->mock(StoreContract::class)))->getName()
        );
    }
}
