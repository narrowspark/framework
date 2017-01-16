<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\PluralCategorys;

use Viserio\Component\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Component\Translation\Traits\NormalizeIntegerValueTrait;

class Slovenian implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: sl
     *
     * Languages:
     * - Slovenian (sl)
     *
     * Rules:
     *  one   → n mod 100 is 1;
     *  two   → n mod 100 is 2;
     *  few   → n mod 100 in 3..4;
     *  other → everything else
     *
     * @param int|string $count
     *
     * @return int
     */
    public function category($count): int
    {
        $count = $this->normalizeInteger($count);

        if (! is_float($count) && $count % 100 === 1) {
            return 0;
        } elseif (! is_float($count) && $count % 100 === 2) {
            return 1;
        } elseif (
            (! is_float($count) &&
            ($i = $count % 100) >= 3 && $i <= 4)
        ) {
            return 2;
        }

        return 3;
    }
}
