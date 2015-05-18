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
 * MacedonianTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class MacedonianTest extends AbstractPluralCategorysTest
{
    public function category()
    {
        return [
            [1, 'one'],
            ['1', 'one'],
            [1.0, 'one'],
            ['1.0', 'one'],
            [21, 'one'],
            [31, 'one'],
            [41, 'one'],
            [51, 'one'],
            [101, 'one'],
            [0, 'other'],
            [2, 'other'],
            [3, 'other'],
            [5, 'other'],
            [9, 'other'],
            [11, 'other'],
            [13, 'other'],
            [14, 'other'],
            [18, 'other'],
            [19, 'other'],
            [20, 'other'],
            [22, 'other'],
            [25, 'other'],
            [30, 'other'],
            [32, 'other'],
            [40, 'other'],
            [0.31, 'other'],
            [1.31, 'other'],
            [1.99, 'other'],
        ];
    }
}
