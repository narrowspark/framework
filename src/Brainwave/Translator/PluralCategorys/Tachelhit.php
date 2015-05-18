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
 * @version     0.9.8-dev
 */

use Brainwave\Contracts\Translator\PluralCategory as CategoryContract;
use Brainwave\Translator\Traits\IntegerRuleTrait;

/**
 * Tachelhit.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class Tachelhit implements CategoryContract
{
    use IntegerRuleTrait;

    /**
     * Returns category key by count.
     *
     * Locales: shi
     *
     * Languages:
     * - Tachelhit (shi)
     *
     * Rules:
     *  one   → n within 0..1;
     *  few   → n in 2..10;
     *  other → everything else
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        if ($count >= 0 && $count <= 1) {
            return 'one';
        } elseif ($this->isInteger($count) && $count >= 2 && $count <= 10) {
            return 'few';
        }

        return 'other';
    }
}
