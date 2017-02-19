<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\PluralCategorys;

use Viserio\Component\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Component\Translation\Traits\NormalizeIntegerValueTrait;

class Two implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: iu kw naq se sma smi smj smn sms
     *
     * Languages:
     *  Inuktitut (iu)
     *  Cornish (kw)
     *  Nama (naq)
     *  Northern Sami (se)
     *  Southern Sami (sma)
     *  Sami Language (smi)
     *  Lule Sami (smj)
     *  Inari Sami (smn)
     *  Skolt Sami (sms)
     *
     * Rules:
     *  one   → n is 1;
     *  two   → n is 2;
     *  other → everything else
     *
     * @param int|string $count
     *
     * @return int
     */
    public function category($count): int
    {
        $count = $this->normalizeInteger($count);

        if ($count === 1) {
            return 0;
        } elseif ($count === 2) {
            return 1;
        }

        return 2;
    }
}
