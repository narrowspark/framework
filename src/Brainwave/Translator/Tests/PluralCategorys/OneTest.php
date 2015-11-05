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
 * OneTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class OneTest extends AbstractPluralCategorysTest
{
    public function category()
    {
        return [
            [1, 'one'],
            ['1', 'one'],
            [1.0, 'one'],
            ['1.0', 'one'],
            [0, 'other'],
            [10, 'other'],
            [301, 'other'],
            [999, 'other'],
            [1.31, 'other'],
        ];
    }
}
