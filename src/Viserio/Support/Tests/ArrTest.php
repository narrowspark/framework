<?php
namespace Viserio\Support\Test;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0
 */

use Viserio\Support\Arr;

/**
 * ArrTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
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
