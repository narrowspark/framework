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
 * LangiTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class LangiTest extends AbstractPluralCategorysTest
{
    public function category()
    {
        return [
            [0, 'zero'],
            [0.0, 'zero'],
            [0.01, 'one'],
            [0.51, 'one'],
            ['0.71', 'one'],
            [1, 'one'],
            [1.0, 'one'],
            [1.31, 'one'],
            [1.88, 'one'],
            ['1.99', 'one'],
            [2, 'other'],
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
            [2.31, 'other'],
        ];
    }
}
