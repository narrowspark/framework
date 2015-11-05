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
 * TachelhitTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class TachelhitTest extends AbstractPluralCategorysTest
{
    public function category()
    {
        return [
            [0, 'one'],
            ['0', 'one'],
            [0.0, 'one'],
            ['0.0', 'one'],
            [1, 'one'],
            [2, 'few'],
            [3, 'few'],
            [5, 'few'],
            [8, 'few'],
            [10, 'few'],
            ['10', 'few'],
            [10.0, 'few'],
            ['10.0', 'few'],
            [11, 'other'],
            [19, 'other'],
            [69, 'other'],
            [198, 'other'],
            [384, 'other'],
            [999, 'other'],
            [1.31, 'other'],
            [2.31, 'other'],
            [11.31, 'other'],
        ];
    }
}
