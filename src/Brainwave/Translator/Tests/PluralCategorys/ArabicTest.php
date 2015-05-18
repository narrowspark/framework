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
 * ArabicTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class ArabicTest extends AbstractPluralCategorysTest
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
            [2, 'two'],
            [3, 'few'],
            [4, 'few'],
            [6, 'few'],
            [8, 'few'],
            [10, 'few'],
            [103, 'few'],
            ['103', 'few'],
            [103.0, 'few'],
            ['103.0', 'few'],
            [104, 'few'],
            [106, 'few'],
            [108, 'few'],
            [110, 'few'],
            [203, 'few'],
            [204, 'few'],
            [206, 'few'],
            [208, 'few'],
            [210, 'few'],
            [11, 'many'],
            ['11', 'many'],
            [11.0, 'many'],
            [15, 'many'],
            [25, 'many'],
            [36, 'many'],
            [47, 'many'],
            [58, 'many'],
            [69, 'many'],
            [71, 'many'],
            [82, 'many'],
            [93, 'many'],
            [99, 'many'],
            [111, 'many'],
            [115, 'many'],
            [125, 'many'],
            [136, 'many'],
            [147, 'many'],
            [158, 'many'],
            [169, 'many'],
            [171, 'many'],
            [182, 'many'],
            [193, 'many'],
            [199, 'many'],
            [211, 'many'],
            [215, 'many'],
            [225, 'many'],
            [236, 'many'],
            [247, 'many'],
            [258, 'many'],
            [269, 'many'],
            [271, 'many'],
            [282, 'many'],
            [293, 'many'],
            [299, 'many'],
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
            [1.31, 'other'],
            [2.31, 'other'],
            [3.31, 'other'],
            [11.31, 'other'],
            [100.31, 'other'],
            [103.1, 'other'],
        ];
    }
}
