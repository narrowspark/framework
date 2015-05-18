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
 * TamazightTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class TamazightTest extends AbstractPluralCategorysTest
{
    public function category()
    {
        return [
            [0, 'one'],
            [1, 'one'],
            ['1', 'one'],
            [1.0, 'one'],
            ['1.0', 'one'],
            [11, 'one'],
            [12, 'one'],
            [19, 'one'],
            [20, 'one'],
            [21, 'one'],
            [32, 'one'],
            [43, 'one'],
            [54, 'one'],
            [65, 'one'],
            [76, 'one'],
            [87, 'one'],
            [98, 'one'],
            [99, 'one'],
            [100, 'other'],
            [101, 'other'],
            [102, 'other'],
            [200, 'other'],
            [201, 'other'],
            [202, 'other'],
            [300, 'other'],
            [301, 'other'],
            [302, 'other'],
            [0.31, 'other'],
            [1.2, 'other'],
            [2.07, 'other'],
            [3.31, 'other'],
            [11.31, 'other'],
            [100.31, 'other'],
        ];
    }
}
