<?php
declare(strict_types=1);
namespace Viserio\Routing;

final class Pattern
{
    const ANY = '.+';

    const ALPHA = '[a-zA-Z]+';

    const ALPHA_NUM = '[a-zA-Z\d]+';

    const ALPHA_NUM_DASH = '[a-zA-Z\d\-]+';

    const ALPHA_UPPER = '[A-Z]+';

    const ALPHA_LOWER = '[a-z]+';

    const DIGITS = '\d+';

    const NUMBER = '[0-9]+';

    const WORD = '[a-zA-Z]+';

    const SLUG = '[a-z0-9-]+';

    const UUID = '[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}+';

    /**
     * Generate a regex for a string.
     *
     * @param string $pattern
     *
     * @return string
     */
    public static function asRegex(string $pattern): string
    {
        return '/^(' . $pattern . ')$/';
    }

    /**
     * Don't instantiate this class.
     */
    private function __construct() {
        //
    }
}
