<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\PluralCategorys;

use Viserio\Component\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Component\Translation\Traits\NormalizeIntegerValueTrait;

class Polish implements CategoryContract
{
    use NormalizeIntegerValueTrait;

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
     * @param int|string $count
     *
     * @return int
     */
    public function category($count): int
    {
        $count = $this->normalizeInteger($count);

        $i10 = $count % 10;
        $i   = $count % 100;

        if ($count === 1) {
            return 0;
        } elseif (! is_float($count) &&
            $i10 >= 2 &&
            $i10 <= 4 &&
            ! ($i >= 12 && $i <= 14) &&
            ! ($i >= 22 && $i <= 24)
        ) {
            return 1;
        }

        return 2;
    }
}
