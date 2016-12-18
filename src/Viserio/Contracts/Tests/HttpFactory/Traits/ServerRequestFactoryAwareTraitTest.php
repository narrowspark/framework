<?php
declare(strict_types=1);
namespace Viserio\Contracts\HttpFactory\Tests\Traits;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\HttpFactory\Traits\ServerRequestFactoryAwareTrait;
use Interop\Http\Factory\ServerRequestInterface;

class ServerRequestFactoryAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;
    use ServerRequestFactoryAwareTrait;

    public function testSetAndGetServerRequestFactory()
    {
        $this->setServerRequest($this->mock(ServerRequestInterface::class));

        $this->assertInstanceOf(ServerRequestInterface::class, $this->getServerRequest());
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
