<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Container\Tests\Traits;

use Interop\Container\ContainerInterface;
use Narrowspark\TestingHelper\ArrayContainer;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;

class ContainerAwareTraitTest extends TestCase
{
    use ContainerAwareTrait;

    public function testGetAndSetContainer()
    {
        $this->setContainer(new ArrayContainer());

        self::assertInstanceOf(ContainerInterface::class, $this->getContainer());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Container is not set up.
     */
    public function testGetContainerThrowExceptionIfContainerIsNotSet()
    {
        $this->getContainer();
    }
}
