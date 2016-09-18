<?php
declare(strict_types=1);
namespace Viserio\Contracts\Container\Tests\Traits;

use Narrowspark\TestingHelper\ArrayContainer;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;

class ContainerAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    use ContainerAwareTrait;

    public function testGetAndSetContainer()
    {
        $this->setContainer(new ArrayContainer());

        $this->assertInstanceOf('\Interop\Container\ContainerInterface', $this->getcontainer());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Container is not set up.
     */
    public function testGetSetContainerThrowExceptionIfContainerIsNotSet()
    {
        $this->getcontainer();
    }
}
