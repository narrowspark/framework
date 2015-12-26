<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\NormalizeIntegerValueTrait;

class Gaelic implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: gd
     *
     * Languages:
     * - Scottish Gaelic (gd)
     *
     * Rules:
     *  one → n in 1,11;
     *  two → n in 2,12;
     *  few → n in 3..10,13..19;
     *  other → everything else
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        $count = $this->normalizeInteger($count);

        if ($count === 1 || $count === 11) {
            return 'one';
        } elseif ($count === 2 || $count === 12) {
            return 'two';
        } elseif (
            !is_float($count) &&
            (
                ($count >= 3 && $count <= 10) ||
                ($count >= 13 && $count <= 19)
            )
        ) {
            return 'few';
        }

        return 'other';
    }
}
