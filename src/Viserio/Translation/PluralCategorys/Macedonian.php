<?php
namespace Viserio\Translation\PluralCategorys;

use Viserio\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Translation\Traits\NormalizeIntegerValueTrait;

class Macedonian implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: mk
     *
     * Languages:
     * - Macedonian (mk)
     *
     * Rules:
     *  one   → n mod 10 is 1 and n is not 11;
     *  other → everything else
     *
     * @param int|string $count
     *
     * @return int
     */
    public function category($count): int
    {
        $count = $this->normalizeInteger($count);

        if (!is_float($count) && $count % 10 === 1 && $count !== 11) {
            return 0;
        }

        return 1;
    }
}
