<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\PluralCategorys;

use Viserio\Component\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Component\Translation\Traits\NormalizeIntegerValueTrait;

class Romanian implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: ro mo
     *
     * Languages:
     *  Moldavian (mo)
     *  Romanian (ro)
     *
     * Rules:
     *  one   → n is 1;
     *  few   → n is 0 OR n is not 1 AND n mod 100 in 1..19;
     *  other → everything else
     *
     * @param int|string $count
     *
     * @return int
     */
    public function category($count): int
    {
        $count = $this->normalizeInteger($count);

        if (
            $count === 1 ||
            ($count >= 1 && $count <= 1.9)
        ) {
            return 0;
        } elseif (
            $count === 0 ||
            (($i = $count % 100) >= 1 && $i <= 19)
        ) {
            return 1;
        }

        return 2;
    }
}
