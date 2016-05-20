<?php
namespace Viserio\Translation\PluralCategorys;

use Viserio\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Translation\Traits\NormalizeIntegerValueTrait;

class Latvian implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: lv
     *
     * Languages:
     * - Latvian (lv)
     *
     * Rules:
     *  zero  → n is 0;
     *  one   → n mod 10 is 1 and n mod 100 is not 11;
     *  other → everything else
     *
     * @param int $count
     *
     * @return integereger
     */
    public function category($count)
    {
        $count = $this->normalizeInteger($count);

        if ($count === 0) {
            return 0;
        } elseif (!is_float($count) && $count % 10 === 1 && $count % 100 !== 11) {
            return 1;
        }

        return 2;
    }
}
