<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\NormalizeIntegerValueTrait;

class Maltese implements CategoryContract
{
    use NormalizeIntegerValueTrait;

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
     * @param integer $count
     *
     * @return integereger
     */
    public function category($count)
    {
        $count = $this->normalizeInteger($count);

        $i = $count % 100;

        if ($count === 1) {
            return 0;
        } elseif (
            $count === 0 ||
            !is_float($count) && $i >= 2 && $i <= 10
        ) {
            return 1;
        } elseif (!is_float($count) && $i >= 11 && $i <= 19) {
            return 2;
        }

        return 3;
    }
}
