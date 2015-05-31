<?php

namespace Brainwave\Translator\PluralCategorys;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Brainwave\Contracts\Translator\PluralCategory as CategoryContract;
use Brainwave\Translator\Traits\IntegerRuleTrait;

/**
 * Manx.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class Manx implements CategoryContract
{
    use IntegerRuleTrait;

    /**
     * Returns category key by count.
     *
     * Locales: gv
     *
     * Languages:
     * - Manx (gv)
     *
     * Rules:
     *  one   → n mod 10 in 1..2 or n mod 20 is 0;  0-2, 11, 12, 20-22...
     *  other → everything else                     3-10, 13-19, 23-30...; 1.2, 3.07...
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        if ($this->isInteger($count) && (in_array($count % 10, [1, 2], true) || ($count % 20 === 0))) {
            return 'one';
        }

        return 'other';
    }
}
