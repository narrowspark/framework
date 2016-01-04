<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\NormalizeIntegerValueTrait;

class Czech implements CategoryContract
{
    use NormalizeIntegerValueTrait;

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
        $count = $this->normalizeInteger($count);

        if ($count === 1) {
            return 0;
        } elseif (!is_float($count) && $count >= 2 && $count <= 4) {
            return 1;
        }

        return 2;
    }
}
