<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\NormalizeIntegerValueTrait;

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
     * @param integer $count
     *
     * @return integereger
     */
    public function category($count)
    {
        $count = $this->normalizeInteger($count);

        if ($count >= 0 && $count < 2) {
            return 0;
        }

        return 1;
    }
}
