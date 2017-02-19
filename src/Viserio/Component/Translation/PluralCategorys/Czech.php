<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\PluralCategorys;

use Viserio\Component\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Component\Translation\Traits\NormalizeIntegerValueTrait;

class Czech implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: cs sk
     *
     * Languages:
     * - Czech (cs)
     * - Slovak (sk)
     *
     * Rules:
     *  one   → n is 1;
     *  few   → n in 2..4;
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
        } elseif (! is_float($count) && $count >= 2 && $count <= 4) {
            return 1;
        }

        return 2;
    }
}
