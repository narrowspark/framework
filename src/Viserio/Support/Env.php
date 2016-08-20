<?php
declare(strict_types=1);
namespace Viserio\Support;

class Env
{
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return false;
        }

        $value = strtolower($value);

        if (in_array(
            $value,
            [
            'false',
            '(false)',
            'true',
            '(true)',
            'yes',
            '(yes)',
            'no',
            '(no)',
            'on',
            '(on)',
            'off',
            '(off)',
            ]
        )) {
            $value = str_replace(['(', ')'], '', $value);

            return filter_var(
                $value,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        } elseif ($value === 'null' || $value === '(null)') {
            return;
        } elseif (is_numeric($value)) {
            return $value + 0;
        } elseif ($value === 'empty' || $value === '(empty)') {
            return '';
        }

        if (strlen($value) > 1 && mb_substr($value, 0, strlen('"')) === '"' && mb_substr($value, -strlen('"')) === '"') {
            return mb_substr($value, 1, -1);
        }

        return $value;
    }
}
