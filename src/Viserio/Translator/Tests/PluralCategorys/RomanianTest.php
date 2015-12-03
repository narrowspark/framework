<?php
namespace Viserio\Translator\Tests\PluralCategorys;

use Viserio\Translator\PluralCategorys\Romanian;

class RomanianTest extends \PHPUnit_Framework_TestCase
{
    protected $object;

    public function setUp()
    {
        parent::setUp();
        $this->object = new Romanian();
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
            [0, 'few'],
            [2, 'few'],
            ['2', 'few'],
            [2.0, 'few'],
            ['2.0', 'few'],
            [3, 'few'],
            [5, 'few'],
            [9, 'few'],
            [10, 'few'],
            [15, 'few'],
            [18, 'few'],
            [19, 'few'],
            [101, 'few'],
            [109, 'few'],
            [110, 'few'],
            [111, 'few'],
            [117, 'few'],
            [119, 'few'],
            [201, 'few'],
            [209, 'few'],
            [210, 'few'],
            [211, 'few'],
            [217, 'few'],
            [219, 'few'],
            [20, 'other'],
            [23, 'other'],
            [35, 'other'],
            [89, 'other'],
            [99, 'other'],
            [100, 'other'],
            [120, 'other'],
            [121, 'other'],
            [200, 'other'],
            [220, 'other'],
            [300, 'other'],
            [1.31, 'other'],
            [2.31, 'other'],
            [20.31, 'other'],
        ];
    }
}
