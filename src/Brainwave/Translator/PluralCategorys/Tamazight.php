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
 * Tamazight.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class Tamazight implements CategoryContract
{
    use IntegerRuleTrait;

    /**
     * Returns category key by count.
     *
     * Locales: shi
     *
     * Languages:
     * - Tachelhit (shi)
     *
     * Rules:
     *  one   → n within 0..1;
     *  few   → n in 2..10;
     *  other → everything else
     *
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        if ($this->isInteger($count) && ($count === 0 || $count === 1 || ($count >= 11 && $count <= 99))) {
            return 'one';
        }

        return 'other';
    }
}
