<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;

/**
 * Two.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
class Two implements CategoryContract
{
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
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        if ($count === 1) {
            return 'one';
        } elseif ($count === 2) {
            return 'two';
        }

        return 'other';
    }
}
