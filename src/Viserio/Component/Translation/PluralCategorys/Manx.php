<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\PluralCategorys;

use Viserio\Component\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Component\Translation\Traits\NormalizeIntegerValueTrait;

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
     * @param int|string $count
     *
     * @return int
     */
    public function category($count): int
    {
        $count = $this->normalizeInteger($count);

        if (
            ! is_float($count) &&
            (
                in_array($count % 10, [1, 2], true) ||
                ($count % 20 === 0)
            )
        ) {
            return 0;
        }

        return 1;
    }
}
