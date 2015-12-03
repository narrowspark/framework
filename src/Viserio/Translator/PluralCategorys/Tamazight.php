<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\IntegerRuleTrait;

/**
 * Tamazight.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
class Tamazight implements CategoryContract
{
    use IntegerRuleTrait;

    /**
     * Returns category key by count.
     *
     * Locales: shi
     *
     * Languages:
     * - Tachelhit (shi)
     *
     * Rules:
     *  one   → n within 0..1;
     *  few   → n in 2..10;
     *  other → everything else
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        if ($this->isInteger($count) && ($count === 0 || $count === 1 || ($count >= 11 && $count <= 99))) {
            return 'one';
        }

        return 'other';
    }
}
