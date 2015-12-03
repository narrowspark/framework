<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\IntegerRuleTrait;

/**
 * Polish.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
class Polish implements CategoryContract
{
    use IntegerRuleTrait;

    /**
     * Returns category key by count.
     *
     * Locales: pl
     *
     * Languages:
     * - Polish (pl)
     *
     * Rules:
     *  one   → n is 1;
     *  few   → n mod 10 in 2..4 and n mod 100 not in 12..14 and n mod 100 not in 22..24;
     *  other → everything else (fractions)
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        $i10 = $count % 10;
        $i = $count % 100;

        if ($count === 1) {
            return 'one';
        } elseif (
            $this->isInteger($count) && ($i10) >= 2 && $i10 <= 4 && !(($i) >= 12 && $i <= 14) && !($i >= 22 && $i <= 24)
        ) {
            return 'few';
        }

        return 'other';
    }
}
