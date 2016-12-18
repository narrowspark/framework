<?php
declare(strict_types=1);
namespace Viserio\Contracts\HttpFactory\Tests\Traits;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\HttpFactory\Traits\UriFactoryAwareTrait;
use Interop\Http\Factory\UriFactoryInterface;

class UriFactoryAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;
    use UriFactoryAwareTrait;

    public function testSetAndGetUriFactory()
    {
        $this->setUriFactory($this->mock(UriFactoryInterface::class));

        $this->assertInstanceOf(UriFactoryInterface::class, $this->getUriFactory());
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
