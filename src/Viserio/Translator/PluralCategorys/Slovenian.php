<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\NormalizeIntegerValueTrait;

class Slovenian implements CategoryContract
{
    use NormalizeIntegerValueTrait;

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
     * @return integereger
     */
    public function category($count)
    {
        $count = $this->normalizeInteger($count);

        if (!is_float($count) && $count % 100 === 1) {
            return 0;
        } elseif (!is_float($count) && $count % 100 === 2) {
            return 1;
        } elseif (
            (!is_float($count) &&
            ($i = $count % 100) >= 3 && $i <= 4)
        ) {
            return 2;
        }

        return 3;
    }
}
