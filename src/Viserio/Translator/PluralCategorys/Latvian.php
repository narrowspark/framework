<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\IntegerRuleTrait;

class Latvian implements CategoryContract
{
    use IntegerRuleTrait;

    /**
     * Returns category key by count.
     *
     * Locales: lv
     *
     * Languages:
     * - Latvian (lv)
     *
     * Rules:
     *  zero  → n is 0;
     *  one   → n mod 10 is 1 and n mod 100 is not 11;
     *  other → everything else
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        if ($count === 0) {
            return 'zero';
        } elseif ($this->isInteger($count) && $count % 10 === 1 && $count % 100 !== 11) {
            return 'one';
        }

        return 'other';
    }
}
