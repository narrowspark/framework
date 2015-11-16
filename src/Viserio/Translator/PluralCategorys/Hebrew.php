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
 * @version     0.10.0-dev
 */

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;

/**
 * Hebrew.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class Hebrew implements CategoryContract
{
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
        if ($count === 1) {
            return 'one';
        }

        if ($count === 2) {
            return 'two';
        } elseif ($count !== 0 && ($count % 10) === 0) {
            return 'many';
        }

        return 'other';
    }
}
