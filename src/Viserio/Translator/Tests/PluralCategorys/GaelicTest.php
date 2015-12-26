<?php
namespace Viserio\Translator\Tests\PluralCategorys;

use Viserio\Translator\PluralCategorys\Gaelic;

class GaelicTest extends \PHPUnit_Framework_TestCase
{
    protected $object;

    public function setUp()
    {
        $this->object = new Gaelic();
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
            [11, 'one'],
            [2, 'two'],
            ['2', 'two'],
            [2.0, 'two'],
            ['2.0', 'two'],
            [12, 'two'],
            [3, 'few'],
            ['3', 'few'],
            [3.0, 'few'],
            ['3.0', 'few'],
            [4, 'few'],
            [6, 'few'],
            [8, 'few'],
            [10, 'few'],
            [13, 'few'],
            [14, 'few'],
            [16, 'few'],
            [18, 'few'],
            [19, 'few'],
            [0, 'other'],
            [20, 'other'],
            [31, 'other'],
            [42, 'other'],
            [53, 'other'],
            [64, 'other'],
            [75, 'other'],
            [86, 'other'],
            [123, 'other'],
            [0.31, 'other'],
            [1.2, 'other'],
            [2.07, 'other'],
            [3.94, 'other'],
            [14.31, 'other'],
            [20.81, 'other'],
            [100.31, 'other'],
        ];
    }
}
