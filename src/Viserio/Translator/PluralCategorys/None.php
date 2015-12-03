<?php
namespace Viserio\Translator\PluralCategorys;

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;

class None implements CategoryContract
{
    /**
     * Returns category key by count.
     *
     * Locales: az bm bo dz fa id ig ii hu ja jv ka kde kea km kn ko lo ms my sah ses sg th to tr vi wo yo zh
     *
     * Languages:
     * - Azerbaijani (az)
     * - Bambara (bm)
     * - Tibetan (bo)
     * - Dzongkha (dz)
     * - Persian (fa)
     * - Indonesian (id)
     * - Igbo (ig)
     * - Sichuan Yi (ii)
     * - Hungarian (hu)
     * - Japanese (ja)
     * - Javanese (jv)
     * - Georgian (ka)
     * - Makonde (kde)
     * - Kabuverdianu (kea)
     * - Khmer (km)
     * - Kannada (kn)
     * - Korean (ko)
     * - Lao (lo)
     * - Malay (ms)
     * - Burmese (my)
     * - Sakha (sah)
     * - Koyraboro Senni (ses)
     * - Sango (sg)
     * - Thai (th)
     * - Tonga (to)
     * - Turkish (tr)
     * - Vietnamese (vi)
     * - Wolof (wo)
     * - Yoruba (yo)
     * - Chinese (zh)
     *
     * These are known to have no plurals, there are no rules:
     *   other → everything
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        return 'other';
    }
}
