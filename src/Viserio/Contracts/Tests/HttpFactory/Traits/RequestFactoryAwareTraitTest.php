<?php
declare(strict_types=1);
namespace Viserio\Contracts\HttpFactory\Tests\Traits;

use Interop\Http\Factory\RequestFactoryInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\HttpFactory\Traits\RequestFactoryAwareTrait;
use PHPUnit\Framework\TestCase;

class RequestFactoryAwareTraitTest extends TestCase
{
    use MockeryTrait;
    use RequestFactoryAwareTrait;

    public function testSetAndGetRequestFactory()
    {
        $this->setRequestFactory($this->mock(RequestFactoryInterface::class));

        static::assertInstanceOf(RequestFactoryInterface::class, $this->getRequestFactory());
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
