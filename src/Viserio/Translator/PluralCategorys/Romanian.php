<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\IntegerRuleTrait;

/**
 * Romanian.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
class Romanian implements CategoryContract
{
    use IntegerRuleTrait;

    /**
     * Returns category key by count.
     *
     * Locales: ro mo
     *
     * Languages:
     *  Moldavian (mo)
     *  Romanian (ro)
     *
     * Rules:
     *  one   → n is 1;
     *  few   → n is 0 OR n is not 1 AND n mod 100 in 1..19;
     *  other → everything else
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        if ($count === 1) {
            return 'one';
        } elseif ($this->isInteger($count) && ($count === 0 || (($i = $count % 100) >= 1 && $i <= 19))) {
            return 'few';
        }

        return 'other';
    }
}
