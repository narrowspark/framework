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
 * ColognianTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class ColognianTest extends AbstractPluralCategorysTest
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
            [2, 'other'],
            [3, 'other'],
            [4, 'other'],
            [5, 'other'],
            [6, 'other'],
            [7, 'other'],
            [9, 'other'],
            [10, 'other'],
            [12, 'other'],
            [14, 'other'],
            [19, 'other'],
            [69, 'other'],
            [198, 'other'],
            [384, 'other'],
            [999, 'other'],
            [0.2, 'other'],
            [1.07, 'other'],
            [2.94, 'other'],
        ];
    }
}
