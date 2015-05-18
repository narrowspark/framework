<?php

namespace Brainwave\Test\Translator\PluralCategorys;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.6-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

/**
 * SlovenianTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class SlovenianTest extends AbstractPluralCategorysTest
{
    public function category()
    {
        return [
            [1, 'one'],
            ['1', 'one'],
            [1.0, 'one'],
            ['1.0', 'one'],
            [101, 'one'],
            [201, 'one'],
            [301, 'one'],
            [401, 'one'],
            [501, 'one'],
            [2, 'two'],
            ['2', 'two'],
            [2.0, 'two'],
            ['2.0', 'two'],
            [102, 'two'],
            [202, 'two'],
            [302, 'two'],
            [402, 'two'],
            [502, 'two'],
            [3, 'few'],
            [4, 'few'],
            ['4', 'few'],
            [4.0, 'few'],
            ['4.0', 'few'],
            [103, 'few'],
            [104, 'few'],
            [203, 'few'],
            [204, 'few'],
            [0, 'other'],
            [5, 'other'],
            [6, 'other'],
            [8, 'other'],
            [10, 'other'],
            [11, 'other'],
            [29, 'other'],
            [60, 'other'],
            [99, 'other'],
            [100, 'other'],
            [105, 'other'],
            [189, 'other'],
            [200, 'other'],
            [205, 'other'],
            [300, 'other'],
            [1.31, 'other'],
            [2.31, 'other'],
            [3.31, 'other'],
            [5.31, 'other'],
        ];
    }
}
