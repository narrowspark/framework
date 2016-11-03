<?php
declare(strict_types=1);
namespace Viserio\Contracts\Container\Tests\Traits;

use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\ArrayContainer;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;

class ContainerAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    use ContainerAwareTrait;

    public function testGetAndSetContainer()
    {
        $this->setContainer(new ArrayContainer());

        $this->assertInstanceOf(ContainerInterface::class, $this->getcontainer());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Container is not set up.
     */
    public function testGetContainerThrowExceptionIfContainerIsNotSet()
    {
        $this->getcontainer();
    }
}
