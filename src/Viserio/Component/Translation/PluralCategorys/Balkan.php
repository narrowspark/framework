<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\PluralCategorys;

use Viserio\Component\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Component\Translation\Traits\NormalizeIntegerValueTrait;

class Balkan implements CategoryContract
{
    use NormalizeIntegerValueTrait;

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
     * @param int|string $count
     *
     * @return int
     */
    public function category($count): int
    {
        $count = $this->normalizeInteger($count);
        $i     = 0;

        if (! is_float($count) && $count % 10 === 1 && $count % 100 !== 11) {
            return 0;
        } elseif (! is_float($count) && ($i = $count % 10) >= 2 && $i <= 4 && ! (($i = $count % 100) >= 12 && $i <= 14)) {
            return 1;
        } elseif (! is_float($count) && ($i = $count % 10) === 0 || ($i >= 5 && $i <= 9) || (($i = $count % 100) >= 11 && $i <= 14)) {
            return 2;
        }

        return 3;
    }
}
