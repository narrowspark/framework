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
}
