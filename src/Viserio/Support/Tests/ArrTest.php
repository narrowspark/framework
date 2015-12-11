<?php
namespace Viserio\Support\Test;

use Viserio\Support\Arr;

class ArrTest extends \PHPUnit_Framework_TestCase
{
    protected $testData = [
        'php' => [
            'rating'    => 5,
            'stars'     => 5,
            'language'  => 'php',
        ],
        'js' => [
            'rating'    => 5,
            'stars'     => 6,
            'language'  => 'js',
        ],
        'css' => [
            'rating'    => 4,
            'stars'     => 4,
            'language'  => 'css',
        ],
        'scss' => [
            'rating'    => 4,
            'stars'     => 4,
            'language'  => 'scss',
        ],
    ];

    public function testGetIndexedByKeysUnique()
    {
        $keysToIndexBy = [
            'rating',
            'stars',
        ];

        $this->assertEquals(
            [
                5 => [
                    5 => [
                        'rating'    => 5,
                        'stars'     => 5,
                        'language'  => 'php',
                    ],
                    6 => [
                        'rating'    => 5,
                        'stars'     => 6,
                        'language'  => 'js',
                    ],
                ],
                4 => [
                    4 => [
                        'rating'    => 4,
                        'stars'     => 4,
                        'language'  => 'scss',
                    ],
                ],
            ],
            Arr::getIndexedByKeys($this->testData, $keysToIndexBy, true)
        );
    }

    public function getIndexedByKeysNonUnique()
    {
        $keysToIndexBy = [
            'rating',
            'stars',
        ];

        $this->assertEquals(
            [
                5 => [
                    5 => [
                        [
                            'rating'    => 5,
                            'stars'     => 5,
                            'language'  => 'php',
                        ],
                    ],
                    6 => [
                        [
                            'rating'    => 5,
                            'stars'     => 6,
                            'language'  => 'js',
                        ],
                    ],
                ],
                4 => [
                    4 => [
                        [
                            'rating'    => 4,
                            'stars'     => 4,
                            'language'  => 'css',
                        ],
                        [
                            'rating'    => 4,
                            'stars'     => 4,
                            'language'  => 'scss',
                        ],
                    ],
                ],
            ],
            Arr::getIndexedByKeys($this->testData, $keysToIndexBy, false)
        );
    }

    public function testGetIndexedValuesString()
    {
        $this->assertEquals(
             [
                 'php'   => 5,
                 'js'    => 6,
                 'css'   => 4,
                 'scss'  => 4,
             ],
             Arr::getIndexedValues($this->testData, 'language', 'stars')
         );
    }

    public function testGetIndexedValuesArray()
    {
        $this->assertEquals(
             [
                 'php' => [
                     'rating'    => 5,
                     'stars'     => 5,
                 ],
                 'js' => [
                     'rating'    => 5,
                     'stars'     => 6,
                 ],
                 'css' => [
                     'rating'    => 4,
                     'stars'     => 4,
                 ],
                 'scss' => [
                     'rating'    => 4,
                     'stars'     => 4,
                 ],
             ],
             Arr::getIndexedValues($this->testData, 'language', ['stars', 'rating'])
         );
    }

    public function testForget()
    {
        $array = ['products' => ['desk' => ['price' => 100]]];
        Arr::forget($array, null);
        $this->assertEquals(['products' => ['desk' => ['price' => 100]]], $array);

        $array = ['products' => ['desk' => ['price' => 100]]];
        Arr::forget($array, []);
        $this->assertEquals(['products' => ['desk' => ['price' => 100]]], $array);

        $array = ['products' => ['desk' => ['price' => 100]]];
        Arr::forget($array, 'products.desk');
        $this->assertEquals(['products' => []], $array);

        $array = ['products' => ['desk' => ['price' => 100]]];
        Arr::forget($array, 'products.desk.price');
        $this->assertEquals(['products' => ['desk' => []]], $array);

        $array = ['products' => ['desk' => ['price' => 100]]];
        Arr::forget($array, 'products.final.price');
        $this->assertEquals(['products' => ['desk' => ['price' => 100]]], $array);

        $array = ['shop' => ['cart' => [150 => 0]]];
        Arr::forget($array, 'shop.final.cart');
        $this->assertEquals(['shop' => ['cart' => [150 => 0]]], $array);

        $array = ['products' => ['desk' => ['price' => ['original' => 50, 'taxes' => 60]]]];
        Arr::forget($array, 'products.desk.price.taxes');
        $this->assertEquals(['products' => ['desk' => ['price' => ['original' => 50]]]], $array);

        $array = ['products' => ['desk' => ['price' => ['original' => 50, 'taxes' => 60]]]];
        Arr::forget($array, 'products.desk.final.taxes');
        $this->assertEquals(['products' => ['desk' => ['price' => ['original' => 50, 'taxes' => 60]]]], $array);

        $array = ['products' => ['desk' => ['price' => 50], null => 'something']];
        Arr::forget($array, ['products.amount.all', 'products.desk.price']);
        $this->assertEquals(['products' => ['desk' => [], null => 'something']], $array);
    }

    public function testExcept()
    {
        $array = ['name' => 'Desk', 'price' => 100];
        $array = Arr::except($array, ['price']);
        $this->assertEquals(['name' => 'Desk'], $array);
        $array = [['name' => 'Desk', 'price' => 100], ['name' => 'Chair', 'price' => 50]];
        $array = Arr::except($array, ['price']);
        $this->assertEquals([['name' => 'Desk'], ['name' => 'Chair']], $array);
    }

    public function testIsColumned()
    {
        $this->assertTrue(Arr::isColumned([['a' => 'a'], ['a' => 'b']]));
        $this->assertTrue(Arr::isColumned([[1 => 'a', 2 => 'b'], [2 => 'a', 1 => 'b']]));
        $this->assertFalse(Arr::isColumned(['a', 'b']));
        $this->assertFalse(Arr::isColumned([['a'], ['b']]));
        $this->assertFalse(Arr::isColumned([['a' => 'a'], ['b' => 'b']]));
    }

    public function testOnly()
    {
        $array = ['name' => 'Desk', 'price' => 100, 'orders' => 10];
        $array = Arr::only($array, ['name', 'price']);
        $this->assertEquals(['name' => 'Desk', 'price' => 100], $array);
        $array = [['name' => 'Desk', 'price' => 100, 'orders' => 10], ['name' => 'Chair', 'price' => 50, 'orders' => 5]];
        $array = Arr::only($array, ['name', 'price']);
        $this->assertEquals([['name' => 'Desk', 'price' => 100], ['name' => 'Chair', 'price' => 50]], $array);
    }
}
