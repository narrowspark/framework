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
 * Czech.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
class Czech implements CategoryContract
{
    use IntegerRuleTrait;

    /**
     * Returns category key by count.
     *
     * Locales: cs sk
     *
     * Languages:
     * - Czech (cs)
     * - Slovak (sk)
     *
     * Rules:
     *  one   → n is 1;
     *  few   → n in 2..4;
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
        } elseif ($this->isInteger($count) && $count >= 2 && $count <= 4) {
            return 'few';
        }

        return 'other';
    }
}
