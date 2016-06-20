<?php
namespace Viserio\Translation\PluralCategorys;

use Viserio\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Translation\Traits\NormalizeIntegerValueTrait;

class Polish implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: pl
     *
     * Languages:
     * - Polish (pl)
     *
     * Rules:
     *  one   → n is 1;
     *  few   → n mod 10 in 2..4 and n mod 100 not in 12..14 and n mod 100 not in 22..24;
     *  other → everything else (fractions)
     *
     * @param int $count
     *
     * @return integereger
     */
    public function category(int $count): string
    {
        $count = $this->normalizeInteger($count);

        $i10 = $count % 10;
        $i   = $count % 100;

        if ($count === 1) {
            return 0;
        } elseif (
<<<<<<< HEAD:src/Viserio/Translation/PluralCategorys/Polish.php
            !is_float($count) && $i10 >= 2 && $i10 <= 4 && !($i >= 12 && $i <= 14) && !($i >= 22 && $i <= 24)
=======
            $this->isInteger($count) && ($i10) >= 2 && $i10 <= 4 && ! (($i) >= 12 && $i <= 14) && ! ($i >= 22 && $i <= 24)
>>>>>>> develop:src/Viserio/Translator/PluralCategorys/Polish.php
        ) {
            return 1;
        }

        return 2;
    }
}
