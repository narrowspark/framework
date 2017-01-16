<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\PluralCategorys;

use Viserio\Component\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Component\Translation\Traits\NormalizeIntegerValueTrait;

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
     * @param int|string $count
     *
     * @return int
     */
    public function category($count): int
    {
        $count = $this->normalizeInteger($count);

        $i = $count % 100;

        if ($count === 1) {
            return 0;
        } elseif (
            $count === 0 ||
            ! is_float($count) && $i >= 2 && $i <= 10
        ) {
            return 1;
        } elseif (! is_float($count) && $i >= 11 && $i <= 19) {
            return 2;
        }

        return 3;
    }
}
