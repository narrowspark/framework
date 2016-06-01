<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;

class Zero implements CategoryContract
{
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
     * @param int $count
     *
     * @return string
     */
    public function category(int $count): string
    {
        if ($count === 0 || $count === 1) {
            return 'one';
        }

        return 'other';
    }
}
