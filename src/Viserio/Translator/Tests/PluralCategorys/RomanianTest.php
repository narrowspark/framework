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
 * RomanianTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class RomanianTest extends AbstractPluralCategorysTest
{
    public function category()
    {
        return [
            [1, 'one'],
            ['1', 'one'],
            [1.0, 'one'],
            ['1.0', 'one'],
            [0, 'few'],
            [2, 'few'],
            ['2', 'few'],
            [2.0, 'few'],
            ['2.0', 'few'],
            [3, 'few'],
            [5, 'few'],
            [9, 'few'],
            [10, 'few'],
            [15, 'few'],
            [18, 'few'],
            [19, 'few'],
            [101, 'few'],
            [109, 'few'],
            [110, 'few'],
            [111, 'few'],
            [117, 'few'],
            [119, 'few'],
            [201, 'few'],
            [209, 'few'],
            [210, 'few'],
            [211, 'few'],
            [217, 'few'],
            [219, 'few'],
            [20, 'other'],
            [23, 'other'],
            [35, 'other'],
            [89, 'other'],
            [99, 'other'],
            [100, 'other'],
            [120, 'other'],
            [121, 'other'],
            [200, 'other'],
            [220, 'other'],
            [300, 'other'],
            [1.31, 'other'],
            [2.31, 'other'],
            [20.31, 'other'],
        ];
    }
}
