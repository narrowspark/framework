<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\PluralCategorys;

use Viserio\Component\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Component\Translation\Traits\NormalizeIntegerValueTrait;

class Hebrew implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: he
     *
     * Languages:
     *  Hebrew (he)
     *
     * Rules:
     *  one   → n is 1;
     *  two   → n is 2;
     *  many  → n is not 0 and n mod 10 is 0;
     *  other → everything else
     *
     * @param int|string $count
     *
     * @return int
     */
    public function category($count): int
    {
        $count = $this->normalizeInteger($count);

        if ($count === 1) {
            return 0;
        } elseif ($count === 2) {
            return 1;
        } elseif ($count !== 0 && ($count % 10) === 0) {
            return 2;
        }

        return 3;
    }
}
