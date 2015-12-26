<?php
namespace Viserio\Translator\Tests\PluralCategorys;

use Viserio\Translator\PluralCategorys\Irish;

class IrishTest extends \PHPUnit_Framework_TestCase
{
    protected $object;

    public function setUp()
    {
        $this->object = new Irish();
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
            [3, 'few'],
            ['3', 'few'],
            [3.0, 'few'],
            ['3.0', 'few'],
            [4, 'few'],
            [5, 'few'],
            [6, 'few'],
            [7, 'many'],
            ['7', 'many'],
            [7.0, 'many'],
            ['7.0', 'many'],
            [8, 'many'],
            [9, 'many'],
            [10, 'many'],
            [0, 'other'],
            [11, 'other'],
            [77, 'other'],
            [301, 'other'],
            [999, 'other'],
            [1.2, 'other'],
            [2.07, 'other'],
            [3.94, 'other'],
            [7.81, 'other'],
            [11.68, 'other'],
        ];
    }
}
