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
 * MalteseTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class MalteseTest extends AbstractPluralCategorysTest
{
    public function category()
    {
        return [
            [1, 'one'],
            ['1', 'one'],
            [1.0, 'one'],
            ['1.0', 'one'],
            [0, 'few'],
            ['0', 'few'],
            [0.0, 'few'],
            ['0.0', 'few'],
            [2, 'few'],
            [3, 'few'],
            [5, 'few'],
            [7, 'few'],
            [9, 'few'],
            [10, 'few'],
            [102, 'few'],
            [103, 'few'],
            [105, 'few'],
            [107, 'few'],
            [109, 'few'],
            [110, 'few'],
            [202, 'few'],
            [203, 'few'],
            [205, 'few'],
            [207, 'few'],
            [209, 'few'],
            [210, 'few'],
            [11, 'many'],
            ['11', 'many'],
            [11.0, 'many'],
            ['11.0', 'many'],
            [12, 'many'],
            [13, 'many'],
            [14, 'many'],
            [16, 'many'],
            [19, 'many'],
            [111, 'many'],
            [112, 'many'],
            [113, 'many'],
            [114, 'many'],
            [116, 'many'],
            [119, 'many'],
            [20, 'other'],
            [21, 'other'],
            [32, 'other'],
            [43, 'other'],
            [83, 'other'],
            [99, 'other'],
            [100, 'other'],
            [101, 'other'],
            [120, 'other'],
            [200, 'other'],
            [201, 'other'],
            [220, 'other'],
            [301, 'other'],
            [1.31, 'other'],
            [2.31, 'other'],
            [11.31, 'other'],
            [20.31, 'other'],
        ];
    }
}
