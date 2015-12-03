<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\IntegerRuleTrait;

/**
 * Macedonian.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
class Macedonian implements CategoryContract
{
    use IntegerRuleTrait;

    /**
     * Returns category key by count.
     *
     * Locales: mk
     *
     * Languages:
     * - Macedonian (mk)
     *
     * Rules:
     *  one   → n mod 10 is 1 and n is not 11;
     *  other → everything else
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        if ($this->isInteger($count) && $count % 10 === 1 && $count !== 11) {
            return 'one';
        }

        return 'other';
    }
}
