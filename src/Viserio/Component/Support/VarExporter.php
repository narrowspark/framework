<?php
declare(strict_types=1);
namespace Viserio\Component\Support;

use Narrowspark\PrettyArray\PrettyArray;
use stdClass;

final class VarExporter
{
    /**
     * Private constructor; non-instantiable.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Converts the supplied value into a valid PHP representation.
     *
     * @param mixed $value
     *
     * @return string
     */
    public static function export($value): string
    {
        if ($value === null) {
            return 'null';
        }

        if (\is_array($value)) {
            return PrettyArray::print($value);
        }

        if (\is_object($value) && $value instanceof stdClass) {
            return '(object)' . self::export((array) $value);
        }

        if (\is_scalar($value)) {
            return \var_export($value, true);
        }

        return 'unserialize(' . \var_export(\serialize($value), true) . ')';
    }
}
