<?php
namespace Viserio\Translator\PluralCategorys;

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

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\IntegerRuleTrait;

/**
 * Maltese.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class Maltese implements CategoryContract
{
    use IntegerRuleTrait;

    /**
     * Returns category key by count.
     *
     * Locales: mt
     *
     * Languages:
     * - Maltese (mt)
     *
     * Rules:
     *  one   → n is 1;
     *  few   → n is 0 or n mod 100 in 2..10;
     *  many  → n mod 100 in 11..19;
     *  other → everything else
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        $isInteger = $this->isInteger($count);
        $i = $count % 100;

        if ($count === 1) {
            return 'one';
        } elseif ($count === 0 || $isInteger && ($i) >= 2 && $i <= 10) {
            return 'few';
        } elseif ($isInteger && ($i) >= 11 && $i <= 19) {
            return 'many';
        }

        return 'other';
    }
}
