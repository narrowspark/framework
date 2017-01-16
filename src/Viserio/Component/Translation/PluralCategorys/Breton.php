<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\PluralCategorys;

use Viserio\Component\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Component\Translation\Traits\NormalizeIntegerValueTrait;

class Breton implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: br
     *
     * Languages:
     * - Breton (br)
     *
     * Rules:
     *  one   → n mod 10 is 1 and n mod 100 not in 11,71,91;
     *  two   → n mod 10 is 2 and n mod 100 not in 12,72,92;
     *  few   → n mod 10 in 3..4,9 and n mod 100 not in 10..19,70..79,90..99;
     *  many  → n mod 1000000 is 0 and n is not 0;
     *  other → everything else
     *
     * @param int|string $count
     *
     * @return int
     */
    public function category($count): int
    {
        $count = $this->normalizeInteger($count);

        if (! is_float($count) && $count % 10 === 1 && ! in_array($count % 100, [11, 71, 91], true)) {
            return 0;
        } elseif (! is_float($count) && $count % 10 === 2 && ! in_array($count % 100, [12, 72, 92], true)) {
            return 1;
        } elseif (! is_float($count) &&
            in_array($count % 10, [3, 4, 9], true) &&
            ! (
                (($i = $count % 100) >= 10 && $i <= 19) ||
                ($i >= 70 && $i <= 79) ||
                ($i >= 90 && $i <= 99)
            )
        ) {
            return 2;
        } elseif ($count !== 0 && $count % 1000000 === 0) {
            return 3;
        }

        return 4;
    }
}
