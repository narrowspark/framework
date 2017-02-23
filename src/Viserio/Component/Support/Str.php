<?php
declare(strict_types=1);
namespace Viserio\Component\Support;

use BadMethodCallException;
use Stringy\StaticStringy;
use Viserio\Component\Contracts\Support\CharacterType;

class Str
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
     * Creates an instance of Stringy and invokes the given method with the
     * rest of the passed arguments. The optional encoding is expected to be
     * the last argument. For example, the following:
     * StaticStringy::slice('fòôbàř', 0, 3, 'UTF-8'); translates to
     * Stringy::create('fòôbàř', 'UTF-8')->slice(0, 3);
     * The result is not cast, so the return value may be of type Stringy,
     * integer, boolean, etc.
     *
     * @param string  $name
     * @param mixed[] $arguments
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        if (class_exists(StaticStringy::class)) {
            return forward_static_call_array([StaticStringy::class, $name], $arguments);
        }
        // @codeCoverageIgnoreStart
        throw new BadMethodCallException($name . ' is not a valid method');
        // @codeCoverageIgnoreEnd
    }

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

        if (! isset($matches[0]) || mb_strlen($value) === mb_strlen($matches[0])) {
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
     * Generate a random string of a given length and character set.
     *
     * @param int    $length     How many characters do you want?
     * @param string $characters Which characters to choose from
     *
     * @return string
     */
    public static function random(
        int $length = 64,
        string $characters = CharacterType::PRINTABLE_ASCII
    ): string {
        $str = '';
        $l   = self::length($characters) - 1;

        for ($i = 0; $i < $length; ++$i) {
            $r = random_int(0, $l);
            $str .= $characters[$r];
        }

        return $str;
    }

    /**
     * Convert a string to snake case.
     *
     * @link https://en.wikipedia.org/wiki/Snake_case
     *
     * @param string $value
     * @param string $delimiter
     *
     * @return string
     */
    public static function snake(string $value, string $delimiter = '_'): string
    {
        $key = $value;

        if (isset(static::$snakeCache[$key][$delimiter])) {
            return static::$snakeCache[$key][$delimiter];
        }

        if (! ctype_lower($value)) {
            $value = self::normalizeScreamingCase($value);
            $value = trim($value);
            $value = (string) static::toLowerCase(preg_replace('/(.)(?=[A-Z0-9])/u', '$1' . $delimiter, $value));
            $value = preg_replace('/[_\s-]+/', $delimiter, $value);
        }

        return static::$snakeCache[$key][$delimiter] = $value;
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

        $value = self::normalizeScreamingCase($value);
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }

    /**
     * Normalize screaming snake/kebab case value to regular snake/kebab case.
     *
     * @param string $value
     *
     * @return string
     */
    private static function normalizeScreamingCase(string $value): string
    {
        if (ctype_upper(str_replace(['_', '-'], '', $value))) {
            return mb_strtolower($value);
        }

        return $value;
    }
}
