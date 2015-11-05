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
 * Welsh.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class Welsh implements CategoryContract
{
    /**
     * Returns category key by count.
     *
     * Locales: cy
     *
     * Languages:
     * - Welsh (cy)
     *
     * Rules:
     *  zero  → n is 0;
     *  one   → n is 1;
     *  two   → n is 2;
     *  few   → n is 3;
     *  many  → n is 6;
     *  other → everything else
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        if ($count === 0) {
            return 'zero';
        } elseif ($count === 1) {
            return 'one';
        } elseif ($count === 2) {
            return 'two';
        } elseif ($count === 3) {
            return 'few';
        } elseif ($count === 6) {
            return 'many';
        }

        return 'other';
    }
}
