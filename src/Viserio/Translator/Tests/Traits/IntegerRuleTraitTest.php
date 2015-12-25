<?php
namespace Viserio\Translator\Tests\Traits;

use Viserio\Translator\Traits\IntegerRuleTrait;

class IntegerRuleTraitTest extends \PHPUnit_Framework_TestCase
{
    use IntegerRuleTrait;

    protected $isInteger;

    protected $object;

    /**
     * @dataProvider provideIsInteger
     */
    public function testIsInt($value, $expected)
    {
        $actual = $this->isInteger($value);
        $this->assertSame($expected, $actual);
    }

    public function provideIsInteger()
    {
        return [
            'integer 0' => [0, true],
            'integer 1' => [1, true],
            'integer 2' => [2, true],
            'float 1.0' => [1.0, false],
            'string 1' => ['1', true],
            'string 1.0' => ['1.0', false],
            'float 1.1' => [1.1, false],
            'string 1.1' => ['1.1', false],
            'string z' => ['z', false],
        ];
    }
}
