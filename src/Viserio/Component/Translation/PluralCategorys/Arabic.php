<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\PluralCategorys;

use Viserio\Component\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Component\Translation\Traits\NormalizeIntegerValueTrait;

class Arabic implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: ar
     *
     * Languages:
     * - Arabic (ar)
     *
     * Rules:
     *  zero  → n is 0;
     *  one   → n is 1;
     *  two   → n is 2;
     *  few   → n mod 100 in 3..10;
     *  many  → n mod 100 in 11..99;
     *  other → everything else
     *
     * @param int|string $count
     *
     * @return int
     */
    public function category($count): int
    {
        $count = $this->normalizeInteger($count);

        if ($count === 0) {
            return 0;
        } elseif ($count === 1) {
            return 1;
        } elseif ($count === 2) {
            return 2;
        } elseif (! is_float($count) && ($i = $count % 100) >= 3 && $i <= 10) {
            return 3;
        } elseif (! is_float($count) && ($i = $count % 100) >= 11 && $i <= 99) {
            return 4;
        }

        return 5;
    }
}
