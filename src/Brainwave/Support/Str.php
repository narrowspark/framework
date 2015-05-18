<?php

namespace Brainwave\Support;

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
 * @version     0.9.8-dev
 */

use Stringy\StaticStringy;

/**
 * Str.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0-dev
 */
class Str extends StaticStringy
{
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
    public static function finish($value, $cap)
    {
        $quoted = preg_quote($cap, '/');

        return preg_replace('/(?:'.$quoted.')+$/', '', $value).$cap;
    }

    /**
     * Determine if a given string matches a given pattern.
     *
     * @param string $pattern
     * @param string $value
     *
     * @return bool
     */
    public static function is($pattern, $value)
    {
        if ($pattern === $value) {
            return true;
        }

        $pattern = preg_quote($pattern, '#');

        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        $pattern = str_replace('\*', '.*', $pattern).'\z';

        return (bool) preg_match('#^'.$pattern.'#', $value);
    }

    /**
     * Limit the number of characters in a string.
     *
     * @param string $value
     * @param int    $limit
     * @param string $end
     * @param bool   $preserveWords
     *
     * @return string
     */
    public static function limit($value, $limit = 100, $end = '...', $preserveWords = false)
    {
        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        if ($preserveWords) {
            $cutArea = mb_substr($value, $limit - 1, 2, 'UTF-8');

            if (strpos($cutArea, ' ') === false) {
                $value = mb_substr($value, 0, $limit, 'UTF-8');
                $spacePos = strrpos($value, ' ');

                if ($spacePos !== false) {
                    return rtrim(mb_substr($value, 0, $spacePos, 'UTF-8')).$end;
                }
            }
        }

        return rtrim(mb_substr($value, 0, $limit, 'UTF-8')).$end;
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
    public static function words($value, $words = 100, $end = '…')
    {
        preg_match('/^\s*+(?:\S++\s*+){1,'.$words.'}/u', $value, $matches);

        if (!isset($matches[0]) || strlen($value) === strlen($matches[0])) {
            return $value;
        }

        return rtrim($matches[0]).$end;
    }

    /**
     * Parse a Class@method style callback into class and method.
     *
     * @param string $callback
     * @param string $default
     *
     * @return array
     */
    public static function parseCallback($callback, $default)
    {
        return static::contains($callback, '@') ? explode('@', $callback, 2) : [$callback, $default];
    }

    /**
     * Generate a more truly "random" alpha-numeric string.
     *
     * @param int $length
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public static function random($length = 16)
    {
        if (function_exists('random_bytes')) {
            $bytes = random_bytes($length * 2);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes($length * 2);
        } else {
            throw new \RuntimeException('OpenSSL extension is required for PHP 5 users.');
        }

        if ($bytes === false) {
            throw new \RuntimeException('Unable to generate random string.');
        }

        return substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $length);
    }

    /**
     * Generate a "random" alpha-numeric string.
     *
     * Should not be considered sufficient for cryptography, etc.
     *
     * @param int $length
     *
     * @return string
     */
    public static function quickRandom($length = 16)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }

    /**
     * Get all of the given string except for a specified string of items.
     *
     * @param string       $value
     * @param string|array $except
     * @param bool         $trim
     *
     * @return string
     */
    public static function except($value, $except, $trim = true)
    {
        $value = str_replace($except, '', $value);

        return ($trim === false) ? $value : trim($value);
    }

    /**
     * Convert a string to snake case.
     *
     * @param string $value
     * @param string $delimiter
     *
     * @return string
     */
    public static function snake($value, $delimiter = '_')
    {
        $key = $value.$delimiter;

        if (isset(static::$snakeCache[$key])) {
            return static::$snakeCache[$key];
        }

        if (!ctype_lower($value)) {
            $value = strtolower(preg_replace('/(.)(?=[A-Z])/', '$1'.$delimiter, $value));
        }

        $value = preg_replace('/([ '.$delimiter.']+)/', $delimiter, $value);

        return static::$snakeCache[$key] = $value;
    }

    /**
     * Convert a value to studly caps case.
     *
     * @param string $value
     *
     * @return string
     */
    public static function studly($value)
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }
}
