<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\HttpFactory\Tests\Traits;

use Interop\Http\Factory\ServerRequestInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\HttpFactory\Traits\ServerRequestFactoryAwareTrait;

class ServerRequestFactoryAwareTraitTest extends MockeryTestCase
{
    use ServerRequestFactoryAwareTrait;

    public function testSetAndGetServerRequestFactory()
    {
        $this->setServerRequest($this->mock(ServerRequestInterface::class));

        static::assertInstanceOf(ServerRequestInterface::class, $this->getServerRequest());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Instance implementing \Interop\Http\Factory\ServerRequestInterface is not set up.
     */
    public function testGetServerRequestThrowExceptionIfEventsDispatcherIsNotSet()
    {
        $this->getServerRequest();
    }
}
