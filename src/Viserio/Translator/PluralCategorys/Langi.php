<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;

class Langi implements CategoryContract
{
    /**
     * Returns category key by count.
     *
     * Locales: lag
     *
     * Languages:
     * - Langi (lag)
     *
     * Rules:
     *  zero  → n is 0;
     *  one   → n within 0..2 and n is not 0 and n is not 2;
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
        } elseif ($count > 0 && $count < 2) {
            return 'one';
        }

        return 'other';
    }
}
