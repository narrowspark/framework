<?php
namespace Viserio\Translator\PluralCategorys;

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
 * @version     0.10.0
 */

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;
use Viserio\Translator\Traits\IntegerRuleTrait;

/**
 * Gaelic.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
class Gaelic implements CategoryContract
{
    use IntegerRuleTrait;

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
        if ($count === 1 || $count === 11) {
            return 'one';
        } elseif ($count === 2 || $count === 12) {
            return 'two';
        } elseif ($this->isInteger($count) && (($count >= 3 && $count <= 10) || ($count >= 13 && $count <= 19))) {
            return 'few';
        }

        return 'other';
    }
}
