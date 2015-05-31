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
 * Latvian.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class Latvian implements CategoryContract
{
    use IntegerRuleTrait;

    /**
     * Returns category key by count.
     *
     * Locales: lv
     *
     * Languages:
     * - Latvian (lv)
     *
     * Rules:
     *  zero  → n is 0;
     *  one   → n mod 10 is 1 and n mod 100 is not 11;
     *  other → everything else
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        if ($count === 0) {
            return 'zero';
        } elseif ($this->isInteger($count) && $count % 10 === 1 && $count % 100 !== 11) {
            return 'one';
        }

        return 'other';
    }
}
