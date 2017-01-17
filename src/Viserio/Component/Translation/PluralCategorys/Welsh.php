<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\PluralCategorys;

use Viserio\Component\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Component\Translation\Traits\NormalizeIntegerValueTrait;

class Welsh implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: cy
     *
     * Languages:
     * - Welsh (cy)
     *
     * Rules:
     *  zero  → n is 0;
     *  one   → n is 1;
     *  two   → n is 2;
     *  few   → n is 3;
     *  many  → n is 6;
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
        } elseif ($count === 3) {
            return 3;
        } elseif ($count === 6) {
            return 4;
        }

        return 5;
    }
}
