<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\PluralCategorys;

use Viserio\Component\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Component\Translation\Traits\NormalizeIntegerValueTrait;

class Zero implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: ak am bh fil tl guw hi ln mg nso ti wa
     *
     * Languages:
     *  Akan (ak)
     *  Amharic (am)
     *  Bihari (bh)
     *  Filipino (fil)
     *  Gun (guw)
     *  Hindi (hi)
     *  Lingala (ln)
     *  Malagasy (mg)
     *  Northern Sotho (nso)
     *  Tigrinya (ti)
     *  Walloon (wa)
     *
     * Rules:
     *  one   → n in 0..1;
     *  other → everything else
     *
     * @param int|string $count
     *
     * @return int
     */
    public function category($count): int
    {
        $count = $this->normalizeInteger($count);

        if ($count === 0 || $count === 1) {
            return 0;
        }

        return 1;
    }
}
