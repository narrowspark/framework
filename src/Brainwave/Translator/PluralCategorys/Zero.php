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
 * @version     0.9.8-dev
 */

use Brainwave\Contracts\Translator\PluralCategory as CategoryContract;

/**
 * Zero.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class Zero implements CategoryContract
{
    /**
     * Returns category key by count.
     *
     * Locales: ak am bh fil tl guw hi ln mg nso ti wa
     *
     * Languages:
     *  Akan (ak)
     *  Amharic (am)
     *  Bihari (bh)
     *  Filipino (fil)
     *  Gun (guw)
     *  Hindi (hi)
     *  Lingala (ln)
     *  Malagasy (mg)
     *  Northern Sotho (nso)
     *  Tigrinya (ti)
     *  Walloon (wa)
     *
     * Rules:
     *  one   → n in 0..1;
     *  other → everything else
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        if ($count === 0 || $count === 1) {
            return 'one';
        }

        return 'other';
    }
}
