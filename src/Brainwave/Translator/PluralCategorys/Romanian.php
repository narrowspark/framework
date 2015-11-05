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
use Brainwave\Translator\Traits\IntegerRuleTrait;

/**
 * Romanian.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class Romanian implements CategoryContract
{
    use IntegerRuleTrait;

    /**
     * Returns category key by count.
     *
     * Locales: ro mo
     *
     * Languages:
     *  Moldavian (mo)
     *  Romanian (ro)
     *
     * Rules:
     *  one   → n is 1;
     *  few   → n is 0 OR n is not 1 AND n mod 100 in 1..19;
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
        } elseif ($this->isInteger($count) && ($count === 0 || (($i = $count % 100) >= 1 && $i <= 19))) {
            return 'few';
        }

        return 'other';
    }
}
