<?php
declare(strict_types=1);
namespace Viserio\Contracts\HttpFactory\Tests\Traits;

use Interop\Http\Factory\StreamFactoryInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\HttpFactory\Traits\StreamFactoryAwareTrait;

class StreamFactoryAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;
    use StreamFactoryAwareTrait;

    public function testSetAndGetStreamFactory()
    {
        $this->setStreamFactory($this->mock(StreamFactoryInterface::class));

        $this->assertInstanceOf(StreamFactoryInterface::class, $this->getStreamFactory());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Instance implementing \Interop\Http\Factory\StreamFactoryInterface is not set up.
     */
    public function testGetStreamFactoryThrowExceptionIfEventsDispatcherIsNotSet()
    {
        $this->getStreamFactory();
    }
}
