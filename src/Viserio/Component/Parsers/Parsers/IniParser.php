<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Parsers;

use Viserio\Component\Contracts\Parsers\Exceptions\ParseException;
use Viserio\Component\Contracts\Parsers\Parser as ParserContract;

class IniParser implements ParserContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        $ini = parse_ini_string($payload, true, INI_SCANNER_RAW);

        if (! $ini) {
            $errors = error_get_last();

            if ($errors === null) {
                $errors['message'] = 'Invalid INI provided.';
            }

            throw new ParseException($errors);
        }

        foreach ($ini as $key => $value) {
            $ini[$key] = self::normalize($value);
        }

        return $ini;
    }

    /**
     * Normalizes INI and other values.
     *
     * @param mixed $value
     *
     * @return bool|int|null|string|array
     */
    private static function normalize($value)
    {
        // Normalize array values
        if (is_array($value)) {
            foreach ($value as &$subValue) {
                $subValue = self::normalize($subValue);
            }

            return $value;
        }

        // Don't normalize non-string value
        if (! is_string($value)) {
            return $value;
        }

        // Normalize true boolean value
        if (self::compareValues($value, ['true', 'on', 'yes'])) {
            return true;
        }

        // Normalize false boolean value
        if (self::compareValues($value, ['false', 'off', 'no', 'none'])) {
            return false;
        }

        // Normalize null value
        if (self::compareValues($value, ['null'])) {
            return null;
        }

        // Normalize numeric value
        if (is_numeric($value)) {
            $numericValue = $value + 0;

            if ((is_int($numericValue) && (int) $value === $numericValue)
                || (is_float($numericValue) && (float) $value === $numericValue)
            ) {
                $value = $numericValue;
            }
        }

        return $value;
    }

    /**
     * Case insensitively compares values.
     *
     * @param string $value
     * @param array  $comparisons
     *
     * @return bool
     */
    private static function compareValues(string $value, array $comparisons): bool
    {
        foreach ($comparisons as $comparison) {
            if (strcasecmp($value, $comparison) === 0) {
                return true;
            }
        }

        return false;
    }
}
