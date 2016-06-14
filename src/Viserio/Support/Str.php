<?php
namespace Viserio\Support;

use ParagonIE\ConstantTime\Binary;
use Stringy\StaticStringy;

class Str extends StaticStringy
{
    const PRINTABLE_ASCII = "\x20\x21\x22\x23\x24\x25\x26\x27\x28\x29\x2a\x2b\x2c\x2d\x2e\x2f".
        "\x30\x31\x32\x33\x34\x35\x36\x37\x38\x39\x3a\x3b\x3c\x3d\x3e\x3f".
        "\x40\x41\x42\x43\x44\x45\x46\x47\x48\x49\x4a\x4b\x4c\x4d\x4e\x4f".
        "\x50\x51\x52\x53\x54\x55\x56\x57\x58\x59\x5a\x5b\x5c\x5d\x5e\x5f".
        "\x60\x61\x62\x63\x64\x65\x66\x67\x68\x69\x6a\x6b\x6c\x6d\x6e\x6f".
        "\x70\x71\x72\x73\x74\x75\x76\x77\x78\x79\x7a\x7b\x7c\x7d\x7e";

    const ALPHANUMERIC = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    const NUMERIC = '0123456789';

    const UPPER_ALPHA = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    const LOWER_ALPHA = 'abcdefghijklmnopqrstuvwxyz';

    /**
     * The cache of snake-cased words.
     *
     * @var array
     */
    protected static $snakeCache = [];

    /**
     * The cache of studly-cased words.
     *
     * @var array
     */
    protected static $studlyCache = [];

    /**
     * Cap a string with a single instance of a given value.
     *
     * @param string $value
     * @param string $cap
     *
     * @return string
     */
    public static function finish(string $value, string $cap): string
    {
        $quoted = preg_quote($cap, '/');

        return preg_replace('/(?:' . $quoted . ')+$/', '', $value) . $cap;
    }

    /**
     * Limit the number of characters in a string.
     *
     * @param string $value
     * @param int    $limit
     * @param string $end
     *
     * @return string
     */
    public static function limit(string $value, int $limit = 100, string $end = '...'): string
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
    }

    /**
     * Limit the number of words in a string.
     *
     * @param string $value
     * @param int    $words
     * @param string $end
     *
     * @return string
     */
    public static function words(string $value, int $words = 100, string $end = '...'): string
    {
        preg_match('/^\s*+(?:\S++\s*+){1,' . $words . '}/u', $value, $matches);

        if (! isset($matches[0]) || strlen($value) === strlen($matches[0])) {
            return $value;
        }

        return rtrim($matches[0]) . $end;
    }

    /**
     * Parse a Class@method style callback into class and method.
     *
     * @param string $callback
     * @param string $default
     *
     * @return array
     */
    public static function parseCallback(string $callback, string $default): array
    {
        return static::contains($callback, '@') ? explode('@', $callback, 2) : [$callback, $default];
    }

    /**
     * Binary-safe substr() implementation
     *
     * @param string   $str
     * @param int      $start
     * @param int|null $length
     *
     * @return string
     */
    public static function subString(string $str, int $start, $length = null): string
    {
        return Binary::safeSubstr($str, $start, $length);
    }

    /**
     * Binary-safe strlen() implementation
     *
     * @param string $str
     *
     * @return int
     */
    public static function stringLength(string $str) : int
    {
        return Binary::safeStrlen($str);
    }

    /**
     * Generate a random string of a given length and character set
     *
     * @param int $length How many characters do you want?
     * @param string $characters Which characters to choose from
     *
     * @return string
     */
    public static function random(
        int $length = 64,
        string $characters = self::PRINTABLE_ASCII
    ): string
    {
        $str = '';
        $l = self::stringLength($characters) - 1;

        for ($i = 0; $i < $length; ++$i) {
            $r = \random_int(0, $l);
            $str .= $characters[$r];
        }

        return $str;
    }

    /**
     * Convert a string to snake case.
     *
     * @param string $value
     * @param string $delimiter
     *
     * @return string
     */
    public static function snake(string $value, string $delimiter = '_'): string
    {
        $key = $value . $delimiter;

        if (isset(static::$snakeCache[$key])) {
            return static::$snakeCache[$key];
        }

        $value = preg_replace('/\s+/', '', $value);
        $value[0] = strtolower($value[0]);
        $len = strlen($value);

        for ($i = 0; $i < $len; ++$i) {
            // See if we have an uppercase character and replace; ord A = 65, Z = 90.
            if (ord($value[$i]) > 64 && ord($value[$i]) < 91) {
                // Replace uppercase of with underscore and lowercase.
                $replace = $delimiter . strtolower($value[$i]);
                $value = substr_replace($value, $replace, $i, 1);

                // Increase length of class and position since we made the string longer.
                ++$len;
                ++$i;
            }
        }

        return static::$snakeCache[$key] = $value;
    }

    /**
     * Convert a value to studly caps case.
     *
     * @param string $value
     *
     * @return string
     */
    public static function studly(string $value): string
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }
}
