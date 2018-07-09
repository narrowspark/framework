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

        static::assertEquals('session', $functions[0]->getName());
        static::assertEquals('get', $functions[0]->getCallable()[1]);

        static::assertEquals('csrf_token', $functions[1]->getName());
        static::assertEquals('getToken', $functions[1]->getCallable()[1]);

        static::assertEquals('csrf_field', $functions[2]->getName());
        static::assertEquals('getCsrfField', $functions[2]->getCallable()[1]);

        static::assertEquals('session_get', $functions[3]->getName());
        static::assertEquals('get', $functions[3]->getCallable()[1]);

        static::assertEquals('session_has', $functions[4]->getName());
        static::assertEquals('has', $functions[4]->getCallable()[1]);
    }

    public function testGetName(): void
    {
        static::assertEquals(
            'Viserio_Bridge_Twig_Extension_Session',
            (new SessionExtension($this->mock(StoreContract::class)))->getName()
        );
    }
}
