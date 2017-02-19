<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\PluralCategorys;

use Viserio\Component\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Component\Translation\Traits\NormalizeIntegerValueTrait;

class French implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: ff fr kab
     *
     * Languages:
     *  Fulah (ff)
     *  French (fr)
     *  Kabyle (kab)
     *
     * Rules:
     *  one   → n within 0..2 and n is not 2;
     *  other → everything else
     *
     * @param int|string $count
     *
     * @return int
     */
    public function category($count): int
    {
        $count = $this->normalizeInteger($count);

        if ($count >= 0 && $count < 2) {
            return 0;
        }

        return 1;
    }
}
