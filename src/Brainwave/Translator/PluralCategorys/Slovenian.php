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
 * Slovenian.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class Slovenian implements CategoryContract
{
    use IntegerRuleTrait;

    /**
     * Returns category key by count.
     *
     * Locales: sl
     *
     * Languages:
     * - Slovenian (sl)
     *
     * Rules:
     *  one   → n mod 100 is 1;
     *  two   → n mod 100 is 2;
     *  few   → n mod 100 in 3..4;
     *  other → everything else
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        $isInteger = $this->isInteger($count);

        if ($isInteger && $count % 100 === 1) {
            return 'one';
        } elseif ($isInteger && $count % 100 === 2) {
            return 'two';
        } elseif ($isInteger && ($i = $count % 100) >= 3 && $i <= 4) {
            return 'few';
        }

        return 'other';
    }
}
