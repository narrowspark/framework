<?php
namespace Viserio\Translator\Traits;

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

/**
 * IntegerRuleTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
trait IntegerRuleTrait
{
    /**
     * Returns TRUE if the value has only integer part and no decimal digits,
     * regardless of the actual type.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isInteger($value)
    {
        return is_numeric($value) && $value - intval($value) === 0;
    }
}
