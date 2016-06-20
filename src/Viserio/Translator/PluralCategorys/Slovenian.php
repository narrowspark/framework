<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\IntegerRuleTrait;

class Slovenian implements CategoryContract
{
    use IntegerRuleTrait;

    /**
     * Returns category key by count.
     *
     * Locales: sl
     *
     * Languages:
     * - Slovenian (sl)
     *
     * Rules:
     *  one   → n mod 100 is 1;
     *  two   → n mod 100 is 2;
     *  few   → n mod 100 in 3..4;
     *  other → everything else
     *
     * @param int $count
     *
     * @return string
     */
    public function category(int $count): string
    {
        $isInteger = $this->isInteger($count);

        if ($isInteger && $count % 100 === 1) {
            return 'one';
        } elseif ($isInteger && $count % 100 === 2) {
            return 'two';
        } elseif ($isInteger && ($i = $count % 100) >= 3 && $i <= 4) {
            return 'few';
        }

        return 'other';
    }
}
