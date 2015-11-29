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
 * @version     0.10.0
 */

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\IntegerRuleTrait;

/**
 * Breton.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
class Breton implements CategoryContract
{
    use IntegerRuleTrait;

    /**
     * Returns category key by count.
     *
     * Locales: br
     *
     * Languages:
     * - Breton (br)
     *
     * Rules:
     *  one   → n mod 10 is 1 and n mod 100 not in 11,71,91;
     *  two   → n mod 10 is 2 and n mod 100 not in 12,72,92;
     *  few   → n mod 10 in 3..4,9 and n mod 100 not in 10..19,70..79,90..99;
     *  many  → n mod 1000000 is 0 and n is not 0;
     *  other → everything else
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        $isInteger = $this->isInteger($count);

        if ($isInteger && $count % 10 === 1 && !in_array($count % 100, [11, 71, 91], true)) {
            return 'one';
        } elseif ($isInteger && $count % 10 === 2 && !in_array($count % 100, [12, 72, 92], true)) {
            return 'two';
        } elseif ($isInteger && in_array($count % 10, [3, 4, 9], true) && !((($i = $count % 100) >= 10 && $i <= 19) || ($i >= 70 && $i <= 79) || ($i >= 90 && $i <= 99))) {
            return 'few';
        } elseif ($count !== 0 && $count % 1000000 === 0) {
            return 'many';
        }

        return 'other';
    }
}
