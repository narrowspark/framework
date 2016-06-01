<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\IntegerRuleTrait;

class Irish implements CategoryContract
{
    use IntegerRuleTrait;

    /**
     * Returns category key by count.
     *
     * Locales: ga
     *
     * Languages:
     *  Irish (ga)
     *
     * Rules:
     *  one   → n is 1;
     *  two   → n is 2;
     *  few   → n in 3..6;
     *  many  → n in 7..10;
     *  other → everything else
     *
     * @param int $count
     *
     * @return string
     */
    public function category(int $count): string
    {
        $isInteger = $this->isInteger($count);

        if ($count === 1) {
            return 'one';
        } elseif ($count === 2) {
            return 'two';
        } elseif ($isInteger && $count >= 3 && $count <= 6) {
            return 'few';
        } elseif ($isInteger && $count >= 7 && $count <= 10) {
            return 'many';
        }

        return 'other';
    }
}
