<?php
declare(strict_types=1);
namespace Viserio\Contracts\HttpFactory\Tests\Traits;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\HttpFactory\Traits\RequestFactoryAwareTrait;
use Interop\Http\Factory\RequestFactoryInterface;

class RequestFactoryAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;
    use RequestFactoryAwareTrait;

    public function testSetAndGetRequestFactory()
    {
        $this->setRequestFactory($this->mock(RequestFactoryInterface::class));

        $this->assertInstanceOf(RequestFactoryInterface::class, $this->getRequestFactory());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Instance implementing \Interop\Http\Factory\RequestFactoryInterface is not set up.
     */
    public function testGetRequestFactoryThrowExceptionIfEventsDispatcherIsNotSet()
    {
        $this->getRequestFactory();
    }
}
