<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Extensions;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Twig\Extensions\SessionExtension;
use Viserio\Component\Contracts\Session\Store as StoreContract;

class SessionExtensionTest extends TestCase
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

    public function testGetName()
    {
        $this->assertEquals(
            'Viserio_Bridge_Twig_Extension_Session',
            (new SessionExtension($this->mock(StoreContract::class)))->getName()
        );
    }
}
