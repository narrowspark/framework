<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;

class Colognian implements CategoryContract
{
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
     * @return string
     */
    public function category(int $count): string
    {
        if ($count === 0) {
            return 'zero';
        }

        if ($count === 1) {
            return 'one';
        }

        return 'other';
    }
}
