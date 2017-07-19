<?php
declare(strict_types=1);
namespace Viserio\Component\Support;

use Doctrine\Common\Inflector\Inflector;

class Pluralizer
{
    /**
     * Uncountable word forms.
     *
     * @var array
     */
    private static $uncountable = [
        'audio',
        'bison',
        'chassis',
        'compensation',
        'coreopsis',
        'data',
        'deer',
        'education',
        'emoji',
        'equipment',
        'evidence',
        'feedback',
        'fish',
        'furniture',
        'gold',
        'information',
        'jedi',
        'knowledge',
        'love',
        'metadata',
        'money',
        'moose',
        'news',
        'nutrition',
        'offspring',
        'plankton',
        'pokemon',
        'police',
        'rain',
        'rice',
        'series',
        'sheep',
        'species',
        'swine',
        'traffic',
        'wheat',
    ];

    /**
     * Private constructor; non-instantiable.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Returns a list of uncountable words.
     *
     * @return array
     */
    public static function getUncountable(): array
    {
        return self::$uncountable;
    }

    /**
     * Get the plural form of an English word.
     *
     * @param string $value
     * @param int    $count
     *
     * @return string
     */
    public static function plural(string $value, int $count = 2): string
    {
        if ($count === 1 || static::uncountable($value)) {
            return $value;
        }

        $plural = Inflector::pluralize($value);

        return static::matchCase($plural, $value);
    }

    /**
     * Get the singular form of an English word.
     *
     * @param string $value
     *
     * @return string
     */
    public static function singular(string $value): string
    {
        $singular = Inflector::singularize($value);

        return static::matchCase($singular, $value);
    }

    /**
     * Determine if the given value is uncountable.
     *
     * @param string $value
     *
     * @return bool
     */
    protected static function uncountable(string $value): bool
    {
        return \in_array(\mb_strtolower($value), static::$uncountable, true);
    }

    /**
     * Attempt to match the case on two strings.
     *
     * @param string $value
     * @param string $comparison
     *
     * @return string
     */
    protected static function matchCase(string $value, string $comparison): string
    {
        $functions = ['mb_strtolower', 'mb_strtoupper', 'ucfirst', 'ucwords'];

        foreach ($functions as $function) {
            if ($function($comparison) === $comparison) {
                return $function($value);
            }
        }

        return $value;
    }
}
