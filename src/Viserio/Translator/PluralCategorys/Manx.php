<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\NormalizeIntegerValueTrait;

class Manx implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: gv
     *
     * Languages:
     * - Manx (gv)
     *
     * Rules:
     *  one   → n mod 10 in 1..2 or n mod 20 is 0;  0-2, 11, 12, 20-22...
     *  other → everything else                     3-10, 13-19, 23-30...; 1.2, 3.07...
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        $count = $this->normalizeInteger($count);

        if (
            !is_float($count) &&
            in_array($count % 10, [1, 2], true) ||
            ($count % 20 === 0)
        ) {
            return 'one';
        }

        return 'other';
    }
}
