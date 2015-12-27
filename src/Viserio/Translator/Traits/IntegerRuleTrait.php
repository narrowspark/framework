<?php
namespace Viserio\Translator\Traits;

trait IntegerRuleTrait
{
    /**
     * Returns TRUE if the value has only integer part and no decimal digits,
     * regardless of the actual type.
     *
     * @param int|string|float $value
     *
     * @return bool
     */
    public function isInteger($value)
    {
        return is_numeric($value) && $value - intval($value) === 0;
    }
}
