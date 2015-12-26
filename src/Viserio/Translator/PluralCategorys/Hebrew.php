<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\NormalizeIntegerValueTrait;

class Hebrew implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: he
     *
     * Languages:
     *  Hebrew (he)
     *
     * Rules:
     *  one   → n is 1;
     *  two   → n is 2;
     *  many  → n is not 0 and n mod 10 is 0;
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
            return 'one';
        } elseif ($count === 2) {
            return 'two';
        } elseif ($count !== 0 && ($count % 10) === 0) {
            return 'many';
        }

        return 'other';
    }
}
