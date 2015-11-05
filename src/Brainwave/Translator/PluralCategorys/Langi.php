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
 * Langi.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class Langi implements CategoryContract
{
    /**
     * Returns category key by count.
     *
     * Locales: lag
     *
     * Languages:
     * - Langi (lag)
     *
     * Rules:
     *  zero  → n is 0;
     *  one   → n within 0..2 and n is not 0 and n is not 2;
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
        } elseif ($count > 0 && $count < 2) {
            return 'one';
        }

        return 'other';
    }
}
