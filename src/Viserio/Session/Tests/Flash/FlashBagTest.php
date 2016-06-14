<?php
namespace Viserio\Session\Tests\Flash;

use Viserio\Session\Flash\FlashBag;
use Viserio\Contracts\Session\FlashBag as FlashBagContract;

class FlashBagTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FlashBagContract
     */
    private $bag;

    /**
     * @var array
     */
    protected $array = [];

    protected function setUp()
    {
        $this->bag = new FlashBag();
        $this->array = ['notice' => ['A previous flash message']];
        $this->bag->initialize($this->array);
    }

    public function tearDown()
    {
        $this->bag = null;
    }

    public function testInitialize()
    {
        $bag = new FlashBag();
        $bag->initialize($this->array);

        $this->assertEquals($this->array, $bag->peekAll());

        $array = ['should' => ['change']];
        $bag->initialize($array);

        $this->assertEquals($array, $bag->peekAll());
    }

    public function testGetStorageKey()
    {
        $this->assertEquals('_v_flashes', $this->bag->getStorageKey());

        $attributeBag = new FlashBag('test');

        $this->assertEquals('test', $attributeBag->getStorageKey());
    }

    public function testGetSetName()
    {
        $this->assertEquals('flashes', $this->bag->getName());
        $this->bag->setName('foo');
        $this->assertEquals('foo', $this->bag->getName());
    }

    public function testPeek()
    {
        $this->assertEquals([], $this->bag->peek('non_existing'));
        $this->assertEquals(['default'], $this->bag->peek('not_existing', ['default']));
        $this->assertEquals(['A previous flash message'], $this->bag->peek('notice'));
        $this->assertEquals(['A previous flash message'], $this->bag->peek('notice'));
    }

    public function testGet()
    {
        $this->assertEquals([], $this->bag->get('non_existing'));
        $this->assertEquals(['default'], $this->bag->get('not_existing', ['default']));
        $this->assertEquals(['A previous flash message'], $this->bag->get('notice'));
        $this->assertEquals([], $this->bag->get('notice'));
    }

    public function testAll()
    {
        $this->bag->set('notice', 'Foo');
        $this->bag->set('error', 'Bar');

        $this->assertEquals(
            [
                'notice' => ['Foo'],
                'error' => ['Bar'],
            ],
            $this->bag->all()
        );
        $this->assertEquals([], $this->bag->all());
    }

    public function testSet()
    {
        $this->bag->set('notice', 'Foo');
        $this->bag->set('notice', 'Bar');

        $this->assertEquals(['Bar'], $this->bag->peek('notice'));
    }

    public function testHas()
    {
        $this->assertFalse($this->bag->has('nothing'));
        $this->assertTrue($this->bag->has('notice'));
    }

    public function testKeys()
    {
        $this->assertEquals(['notice'], $this->bag->keys());
    }

    public function testPeekAll()
    {
        $this->bag->set('notice', 'Foo');
        $this->bag->set('error', 'Bar');

        $this->assertEquals(
            [
                'notice' => ['Foo'],
                'error' => ['Bar'],
            ],
            $this->bag->peekAll()
        );
        $this->assertTrue($this->bag->has('notice'));
        $this->assertTrue($this->bag->has('error'));
        $this->assertEquals(
            [
                'notice' => ['Foo'],
                'error' => ['Bar'],
            ],
            $this->bag->peekAll()
        );
    }
}
