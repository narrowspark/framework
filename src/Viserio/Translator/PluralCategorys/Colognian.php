<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\NormalizeIntegerValueTrait;

class Colognian implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: ksh
     *
     * Languages:
     * - Colognian (ksh)
     *
     * Rules:
     *  zero  → n is 0;
     *  one   → n is 1;
     *  other → everything else
     *
     * @param int $count
     *
     * @return integereger
     */
    public function category($count)
    {
        $count = $this->normalizeInteger($count);

        if ($count === 0) {
            return 0;
        } elseif ($count === 1) {
            return 1;
        }

        return 2;
    }
}
