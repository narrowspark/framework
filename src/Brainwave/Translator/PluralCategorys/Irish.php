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
 * Irish.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class Irish implements CategoryContract
{
    use IntegerRuleTrait;

    /**
     * Returns category key by count.
     *
     * Locales: ga
     *
     * Languages:
     *  Irish (ga)
     *
     * Rules:
     *  one   → n is 1;
     *  two   → n is 2;
     *  few   → n in 3..6;
     *  many  → n in 7..10;
     *  other → everything else
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        $isInteger = $this->isInteger($count);

        if ($count === 1) {
            return 'one';
        } elseif ($count === 2) {
            return 'two';
        } elseif ($isInteger && $count >= 3 && $count <= 6) {
            return 'few';
        } elseif ($isInteger && $count >= 7 && $count <= 10) {
            return 'many';
        }

        return 'other';
    }
}
