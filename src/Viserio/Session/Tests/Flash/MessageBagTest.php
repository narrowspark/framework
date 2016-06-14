<?php
namespace Viserio\Session\Tests\Flash;

use Viserio\Session\Flash\MessageBag;
use Viserio\Contracts\Session\FlashBag as FlashBagContract;

class MessageBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FlashBagContract
     */
    private $bag;

    protected function setUp()
    {
        $this->bag = new MessageBag();
    }

    public function tearDown()
    {
        $this->bag = null;
    }

        public function testGetSetName()
    {
        $this->assertEquals('messages', $this->bag->getName());

        $this->bag->setName('foo');

        $this->assertEquals('foo', $this->bag->getName());
    }
}
