<?php
namespace Viserio\Test\Translator\PluralCategorys;

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
 * LatvianTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class LatvianTest extends AbstractPluralCategorysTest
{
    public function category()
    {
        return [
            [0, 'zero'],
            ['0', 'zero'],
            [0.0, 'zero'],
            ['0.0', 'zero'],
            [1, 'one'],
            ['1', 'one'],
            [1.0, 'one'],
            ['1.0', 'one'],
            [21, 'one'],
            [31, 'one'],
            [41, 'one'],
            [51, 'one'],
            [101, 'one'],
            [2, 'other'],
            [3, 'other'],
            [8, 'other'],
            [10, 'other'],
            [11, 'other'],
            [15, 'other'],
            [19, 'other'],
            [20, 'other'],
            [22, 'other'],
            [30, 'other'],
            [32, 'other'],
            [40, 'other'],
            [111, 'other'],
            [0.31, 'other'],
            [1.31, 'other'],
            [1.99, 'other'],
        ];
    }
}
