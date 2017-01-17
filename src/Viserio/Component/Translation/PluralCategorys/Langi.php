<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\PluralCategorys;

use Viserio\Component\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Component\Translation\Traits\NormalizeIntegerValueTrait;

class Langi implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: lag
     *
     * Languages:
     * - Langi (lag)
     *
     * Rules:
     *  zero  → n is 0;
     *  one   → n within 0..2 and n is not 0 and n is not 2;
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
        } elseif (
            ($count > 0 && $count < 2)
        ) {
            return 1;
        }

        return 2;
    }
}
