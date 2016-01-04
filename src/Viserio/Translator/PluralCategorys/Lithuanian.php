<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\NormalizeIntegerValueTrait;

class Lithuanian implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: lt
     *
     * Languages:
     * - Lithuanian (lt)
     *
     * Rules:
     *  one   → n mod 10 is 1 and n mod 100 not in 11..19;
     *  few   → n mod 10 in 2..9 and n mod 100 not in 11..19;
     *  other → everything else
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        $count = $this->normalizeInteger($count);

        if (!is_float($count) && $count % 10 === 1 && !(($i = $count % 100) >= 11 && $i <= 19)) {
            return 0;
        } elseif (!is_float($count) && ($i = $count % 10) >= 2 && $i <= 9 && !(($i = $count % 100) >= 11 && $i <= 19)) {
            return 1;
        }

        return 2;
    }
}
