<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\PluralCategorys;

use Viserio\Component\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Component\Translation\Traits\NormalizeIntegerValueTrait;

class Tachelhit implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: shi
     *
     * Languages:
     * - Tachelhit (shi)
     *
     * Rules:
     *  one   → n within 0..1;
     *  few   → n in 2..10;
     *  other → everything else
     *
     * @param int|string $count
     *
     * @return int
     */
    public function category($count): int
    {
        $count = $this->normalizeInteger($count);

        if (! is_float($count) && $count >= 0 && $count <= 1) {
            return 0;
        } elseif (
            ! is_float($count) && $count >= 2 && $count <= 10
        ) {
            return 1;
        }

        return 2;
    }
}
