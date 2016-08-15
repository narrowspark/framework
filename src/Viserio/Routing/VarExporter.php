<?php
declare(strict_types=1);
namespace Viserio\Routing;

use StdClass;

class VarExporter
{
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

        if (is_array($value)) {
            if (empty($value)) {
                return '[]';
            } elseif (count($value) === 1) {
                reset($value);

                return '[' . self::export(key($value)) . ' => ' . self::export(current($value)) . ']';
            }

            $code = '[';

            foreach ($value as $key => $element) {
                $code .= self::export($key);
                $code .= ' => ';
                $code .= self::export($element);
                $code .= ',';
            }

            $code .= ']';

            return $code;
        } elseif (is_object($value) && $value instanceof StdClass) {
            return '(object)' . self::export((array) $value);
        }

        if (is_scalar($value)) {
            return var_export($value, true);
        }

        return 'unserialize(' . var_export(serialize($value), true) . ')';
    }

    /**
     * Don't instantiate this class.
     *
     * @codeCoverageIgnore
     */
    private function __construct() {
        //
    }
}
