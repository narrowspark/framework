<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\HttpFactory\Tests\Traits;

use Interop\Http\Factory\UriFactoryInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\HttpFactory\Traits\UriFactoryAwareTrait;

class UriFactoryAwareTraitTest extends MockeryTestCase
{
    use UriFactoryAwareTrait;

    public function testSetAndGetUriFactory()
    {
        $this->setUriFactory($this->mock(UriFactoryInterface::class));

        static::assertInstanceOf(UriFactoryInterface::class, $this->getUriFactory());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Instance implementing [\Interop\Http\Factory\UriFactoryInterface] is not set up.
     */
    public function testGetUriFactoryThrowExceptionIfEventsDispatcherIsNotSet()
    {
        $this->getUriFactory();
    }
}
