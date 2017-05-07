<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\HttpFactory\Tests\Traits;

use Interop\Http\Factory\ResponseFactoryInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\HttpFactory\Traits\ResponseFactoryAwareTrait;

class ResponseFactoryAwareTraitTest extends MockeryTestCase
{
    use ResponseFactoryAwareTrait;

    public function testSetAndGetResponseFactory()
    {
        $this->setResponseFactory($this->mock(ResponseFactoryInterface::class));

        self::assertInstanceOf(ResponseFactoryInterface::class, $this->getResponseFactory());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Instance implementing [\Interop\Http\Factory\ResponseFactoryInterface] is not set up.
     */
    public function testGetResponseFactoryThrowExceptionIfEventsDispatcherIsNotSet()
    {
        $this->getResponseFactory();
    }
}
