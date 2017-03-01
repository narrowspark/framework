<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\HttpFactory\Tests\Traits;

use Interop\Http\Factory\StreamFactoryInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\HttpFactory\Traits\StreamFactoryAwareTrait;

class StreamFactoryAwareTraitTest extends MockeryTestCase
{
    use StreamFactoryAwareTrait;

    public function testSetAndGetStreamFactory()
    {
        $this->setStreamFactory($this->mock(StreamFactoryInterface::class));

        static::assertInstanceOf(StreamFactoryInterface::class, $this->getStreamFactory());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Instance implementing [\Interop\Http\Factory\StreamFactoryInterface] is not set up.
     */
    public function testGetStreamFactoryThrowExceptionIfEventsDispatcherIsNotSet()
    {
        $this->getStreamFactory();
    }
}
