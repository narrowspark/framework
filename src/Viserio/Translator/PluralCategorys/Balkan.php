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
 * Balkan.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
class Balkan implements CategoryContract
{
    use IntegerRuleTrait;

    /**
     * Returns category key by count.
     *
     * Locales: be bs hr ru sh sr uk
     *
     * Languages:
     * - Belarusian (be)
     * - Bosnian (bs)
     * - Croatian (hr)
     * - Russian (ru)
     * - Serbo-Croatian (sh)
     * - Serbian (sr)
     * - Ukrainian (uk)
     *
     * Rules:
     *  one   → n mod 10 is 1 and n mod 100 is not 11;
     *  few   → n mod 10 in 2..4 and n mod 100 not in 12..14;
     *  many  → n mod 10 is 0 or n mod 10 in 5..9 or n mod 100 in 11..14;
     *  other → everything else (fractions)
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        $isInteger = $this->isInteger($count);

        if ($isInteger && $count % 10 === 1 && $count % 100 !== 11) {
            return 'one';
        } elseif ($isInteger && ($i = $count % 10) >= 2 && $i <= 4 && !(($i = $count % 100) >= 12 && $i <= 14)) {
            return 'few';
        } elseif ($isInteger && (($i = $count % 10) === 0 || ($i >= 5 && $i <= 9) || (($i = $count % 100) >= 11 && $i <= 14))) {
            return 'many';
        }

        return 'other';
    }
}
