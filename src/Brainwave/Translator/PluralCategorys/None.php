<?php
namespace Brainwave\Translator\PluralCategorys;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Brainwave\Contracts\Translator\PluralCategory as CategoryContract;

/**
 * None.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
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
