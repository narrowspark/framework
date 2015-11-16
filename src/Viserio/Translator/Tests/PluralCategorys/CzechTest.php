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
 * CzechTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class CzechTest extends AbstractPluralCategorysTest
{
    public function category()
    {
        return [
            [1, 'one'],
            ['1', 'one'],
            [1.0, 'one'],
            ['1.0', 'one'],
            [2, 'few'],
            ['2', 'few'],
            [2.0, 'few'],
            ['2.0', 'few'],
            [3, 'few'],
            [4, 'few'],
            [0, 'other'],
            [5, 'other'],
            [7, 'other'],
            [17, 'other'],
            [28, 'other'],
            [39, 'other'],
            [40, 'other'],
            [51, 'other'],
            [63, 'other'],
            [111, 'other'],
            [597, 'other'],
            [846, 'other'],
            [999, 'other'],
            [1.31, 'other'],
            [2.31, 'other'],
            [5.31, 'other'],
        ];
    }
}
