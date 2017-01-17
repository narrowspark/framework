<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\PluralCategorys;

use Viserio\Component\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Component\Translation\Traits\NormalizeIntegerValueTrait;

class None implements CategoryContract
{
    use NormalizeIntegerValueTrait;

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
     * @param int|string $count
     *
     * @return int
     */
    public function category($count): int
    {
        return 0;
    }
}
