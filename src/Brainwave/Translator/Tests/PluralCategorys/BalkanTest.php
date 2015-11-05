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
 * BalkanTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class BalkanTest extends AbstractPluralCategorysTest
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
            [61, 'one'],
            [2, 'few'],
            ['2', 'few'],
            [2.0, 'few'],
            ['2.0', 'few'],
            [3, 'few'],
            [4, 'few'],
            [22, 'few'],
            [23, 'few'],
            [24, 'few'],
            [142, 'few'],
            [143, 'few'],
            [144, 'few'],
            [0, 'many'],
            [5, 'many'],
            ['5', 'many'],
            [5.0, 'many'],
            ['5.0', 'many'],
            [6, 'many'],
            [7, 'many'],
            [11, 'many'],
            [12, 'many'],
            [15, 'many'],
            [20, 'many'],
            [25, 'many'],
            [26, 'many'],
            [28, 'many'],
            [29, 'many'],
            [30, 'many'],
            [65, 'many'],
            [66, 'many'],
            [68, 'many'],
            [69, 'many'],
            [70, 'many'],
            [112, 'many'],
            [1112, 'many'],
            [1.31, 'other'],
            [2.31, 'other'],
            [5.31, 'other'],
        ];
    }
}
