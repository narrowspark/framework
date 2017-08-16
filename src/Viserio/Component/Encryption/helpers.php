<?php
declare(strict_types=1);

use Viserio\Component\Contracts\Encryption\Exception\CannotPerformOperation;

if (! \function_exists('str_cpy')) {
    /**
     * PHP 7 uses interned strings. We don't want altering this one to alter
     * the original string.
     *
     * @param string $string
     *
     * @throws \Viserio\Component\Contracts\Encryption\Exception\CannotPerformOperation
     *
     * @return string
     */
    function str_cpy(string $string): string
    {
        $length = \mb_strlen($string, '8bit');

        if ($length === false) {
            throw new CannotPerformOperation('mb_strlen() failed unexpectedly.');
        }

        $return = '';

        for ($i = 0; $i < $length; ++$i) {
            $return .= $string[$i];
        }

        return $return;
    }
}

if (! \function_exists('substr')) {
    /**
     * PHP 7 uses interned strings. We don't want altering this one to alter
     * the original string.
     *
     * @param string $string
     *
     * @throws \Viserio\Component\Contracts\Encryption\Exception\CannotPerformOperation
     *
     * @return string
     */
    function str_cpy(string $string): string
    {
        $length = \mb_strlen($str, '8bit');

        if ($length === false) {
            throw new CannotPerformOperation('mb_strlen() failed unexpectedly.');
        }

        $return = '';

        for ($i = 0; $i < $length; ++$i) {
            $return .= $string[$i];
        }

        return $return;
    }
}