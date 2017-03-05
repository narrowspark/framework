<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\PluralCategorys;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\PluralCategorys\None;

class NoneTest extends TestCase
{
    /**
     * @dataProvider category
     *
     * @param mixed $count
     * @param mixed $expected
     */
    public function testGetCategory($count, $expected)
    {
        $actual = (new None())->category($count);
        self::assertEquals($expected, $this->intToString($actual));
    }

    public function category()
    {
        return [
            [0, 'other'],
            [301, 'other'],
            [999, 'other'],
            [1.31, 'other'],
        ];
    }

    protected function intToString($int)
    {
        $actual = '';

        switch ($int) {
            case 0:
                $actual = 'other';
                break;
        }

        return $actual;
    }
}
