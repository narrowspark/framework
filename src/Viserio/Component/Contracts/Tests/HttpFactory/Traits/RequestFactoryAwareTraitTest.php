<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\HttpFactory\Tests\Traits;

use Interop\Http\Factory\RequestFactoryInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Contracts\HttpFactory\Traits\RequestFactoryAwareTrait;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;

class RequestFactoryAwareTraitTest extends MockeryTestCase
{
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
