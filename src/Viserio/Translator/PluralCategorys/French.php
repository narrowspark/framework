<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;

class French implements CategoryContract
{
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
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        if ($count >= 0 && $count < 2) {
            return 'one';
        }

        return 'other';
    }
}
