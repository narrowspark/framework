<?php
namespace Viserio\Translator\Tests\PluralCategorys;

use Viserio\Translator\PluralCategorys\Hebrew;

class HebrewTest extends \PHPUnit_Framework_TestCase
{
    protected $object;

    public function setUp()
    {
        $this->object = new Hebrew();
    }

    /**
     * @dataProvider category
     */
    public function testGetCategory($count, $expected)
    {
        $actual = $this->object->category($count);
        $this->assertEquals($expected, $actual);
    }

    public function category()
    {
        return [
            [1, 'one'],
            ['1', 'one'],
            [1.0, 'one'],
            ['1.0', 'one'],
            [2, 'two'],
            ['2', 'two'],
            [2.0, 'two'],
            ['2.0', 'two'],
            [10, 'many'],
            ['10', 'many'],
            [10.0, 'many'],
            ['10.0', 'many'],
            [20, 'many'],
            [100, 'many'],
            [0, 'other'],
            [3, 'other'],
            [9, 'other'],
            [77, 'other'],
            [301, 'other'],
            [999, 'other'],
            [1.31, 'other'],
            [5.45, 'other'],
        ];
    }
}
