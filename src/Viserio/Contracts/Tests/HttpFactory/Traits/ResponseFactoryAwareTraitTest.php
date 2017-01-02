<?php
declare(strict_types=1);
namespace Viserio\Contracts\HttpFactory\Tests\Traits;

use Interop\Http\Factory\ResponseFactoryInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\HttpFactory\Traits\ResponseFactoryAwareTrait;
use PHPUnit\Framework\TestCase;

class ResponseFactoryAwareTraitTest extends TestCase
{
    use MockeryTrait;
    use ResponseFactoryAwareTrait;

    public function testSetAndGetResponseFactory()
    {
        $this->setResponseFactory($this->mock(ResponseFactoryInterface::class));

        static::assertInstanceOf(ResponseFactoryInterface::class, $this->getResponseFactory());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Instance implementing \Interop\Http\Factory\ResponseFactoryInterface is not set up.
     */
    public function testGetResponseFactoryThrowExceptionIfEventsDispatcherIsNotSet()
    {
        $this->getResponseFactory();
    }
}
