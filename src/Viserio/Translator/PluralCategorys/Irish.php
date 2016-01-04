<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\NormalizeIntegerValueTrait;

class Irish implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: ga
     *
     * Languages:
     *  Irish (ga)
     *
     * Rules:
     *  one   → n is 1;
     *  two   → n is 2;
     *  few   → n in 3..6;
     *  many  → n in 7..10;
     *  other → everything else
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        $count = $this->normalizeInteger($count);

        if ($count === 1) {
            return 0;
        } elseif ($count === 2) {
            return 1;
        } elseif (!is_float($count) && $count >= 3 && $count <= 6) {
            return 2;
        } elseif (!is_float($count) && $count >= 7 && $count <= 10) {
            return 3;
        }

        return 4;
    }
}
