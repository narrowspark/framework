<?php
namespace Viserio\Support\Tests\Traits;

use Viserio\Support\Traits\ContainerAwareTrait;
use Narrowspark\TestingHelper\ArrayContainer;

class ContainerAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    use ContainerAwareTrait;

    public function testGetAndSetContainer()
    {
        $this->setContainer(new ArrayContainer());

        $this->assertInstanceOf('\Interop\Container\ContainerInterface', $this->getcontainer());
    }
}
