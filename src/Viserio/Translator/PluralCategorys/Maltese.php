<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\IntegerRuleTrait;

class Maltese implements CategoryContract
{
    use IntegerRuleTrait;

    /**
     * Returns category key by count.
     *
     * Locales: mt
     *
     * Languages:
     * - Maltese (mt)
     *
     * Rules:
     *  one   → n is 1;
     *  few   → n is 0 or n mod 100 in 2..10;
     *  many  → n mod 100 in 11..19;
     *  other → everything else
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        $isInteger = $this->isInteger($count);
        $i = $count % 100;

        if ($count === 1) {
            return 'one';
        } elseif ($count === 0 || $isInteger && ($i) >= 2 && $i <= 10) {
            return 'few';
        } elseif ($isInteger && ($i) >= 11 && $i <= 19) {
            return 'many';
        }

        return 'other';
    }
}
