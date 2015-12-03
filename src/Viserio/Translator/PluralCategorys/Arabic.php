<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\IntegerRuleTrait;

/**
 * Arabic.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
class Arabic implements CategoryContract
{
    use IntegerRuleTrait;

    /**
     * Returns category key by count.
     *
     * Locales: ar
     *
     * Languages:
     * - Arabic (ar)
     *
     * Rules:
     *  zero  → n is 0;
     *  one   → n is 1;
     *  two   → n is 2;
     *  few   → n mod 100 in 3..10;
     *  many  → n mod 100 in 11..99;
     *  other → everything else
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        $isInteger = $this->isInteger($count);

        if ($count === 0) {
            return 'zero';
        } elseif ($count === 1) {
            return 'one';
        } elseif ($count === 2) {
            return 'two';
        } elseif ($isInteger && ($i = $count % 100) >= 3 && $i <= 10) {
            return 'few';
        } elseif ($isInteger && ($i = $count % 100) >= 11 && $i <= 99) {
            return 'many';
        }

        return 'other';
    }
}
