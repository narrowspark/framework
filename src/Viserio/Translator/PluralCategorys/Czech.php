<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\IntegerRuleTrait;

class Czech implements CategoryContract
{
    use IntegerRuleTrait;

    /**
     * Returns category key by count.
     *
     * Locales: cs sk
     *
     * Languages:
     * - Czech (cs)
     * - Slovak (sk)
     *
     * Rules:
     *  one   → n is 1;
     *  few   → n in 2..4;
     *  other → everything else
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        if ($count === 1) {
            return 'one';
        } elseif ($this->isInteger($count) && $count >= 2 && $count <= 4) {
            return 'few';
        }

        return 'other';
    }
}
