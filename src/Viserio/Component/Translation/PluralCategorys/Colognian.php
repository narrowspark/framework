<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\PluralCategorys;

use Viserio\Component\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Component\Translation\Traits\NormalizeIntegerValueTrait;

class Colognian implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: ksh
     *
     * Languages:
     * - Colognian (ksh)
     *
     * Rules:
     *  zero  → n is 0;
     *  one   → n is 1;
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
        }

        return 2;
    }
}
