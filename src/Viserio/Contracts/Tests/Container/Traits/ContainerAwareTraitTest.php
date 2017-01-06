<?php
declare(strict_types=1);
namespace Viserio\Contracts\Container\Tests\Traits;

use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\ArrayContainer;
use PHPUnit\Framework\TestCase;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;

class ContainerAwareTraitTest extends TestCase
{
    use ContainerAwareTrait;

    public function testGetAndSetContainer()
    {
        $this->setContainer(new ArrayContainer());

        self::assertInstanceOf(ContainerInterface::class, $this->getcontainer());
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
