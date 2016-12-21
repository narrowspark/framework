<?php
declare(strict_types=1);
namespace Viserio\Contracts\HttpFactory\Tests\Traits;

use Interop\Http\Factory\UriFactoryInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\HttpFactory\Traits\UriFactoryAwareTrait;

class UriFactoryAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;
    use UriFactoryAwareTrait;

    public function testSetAndGetUriFactory()
    {
        $this->setUriFactory($this->mock(UriFactoryInterface::class));

        static::assertInstanceOf(UriFactoryInterface::class, $this->getUriFactory());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Instance implementing \Interop\Http\Factory\UriFactoryInterface is not set up.
     */
    public function testGetUriFactoryThrowExceptionIfEventsDispatcherIsNotSet()
    {
        $this->getUriFactory();
    }
}
