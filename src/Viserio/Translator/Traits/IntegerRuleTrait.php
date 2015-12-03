<?php
namespace Viserio\Translator\Traits;

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
