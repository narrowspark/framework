<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Traits;

trait ArrayPrettyPrintTrait
{
    /**
     * Make php array pretty for save or output.
     *
     * @param iterable $config
     * @param in       $indentLevel
     *
     * @return string
     */
    protected function getPrettyPrintArray(array $config, int $indentLevel = 1): string
    {
        $indent  = str_repeat(' ', $indentLevel * 4);
        $entries = [];

        foreach ($config as $key => $value) {
            if (! is_int($key)) {
                if (is_string($key) && (class_exists($key) || interface_exists($key)) && ctype_upper($key[0])) {
                    $key = sprintf('\\%s::class', ltrim($key, '\\'));
                } else {
                    $key = sprintf("'%s'", $key);
                }
            }

            $entries[] = sprintf(
                '%s%s%s,',
                $indent,
                sprintf('%s => ', $key),
                $this->createValue($value, $indentLevel)
            );
        }

        $outerIndent = str_repeat(' ', ($indentLevel - 1) * 4);

        return sprintf("[\n%s\n%s]", implode("\n", $entries), $outerIndent);
    }

    /**
     * Create the right value.
     *
     * @param mixed $value
     * @param int   $indentLevel
     *
     * @return string
     */
    protected function createValue($value, int $indentLevel): string
    {
        if (is_array($value)) {
            return $this->getPrettyPrintArray($value, $indentLevel + 1);
        }

        if (is_string($value) && (class_exists($value) || interface_exists($value)) && ctype_upper($value[0])) {
            return sprintf('\\%s::class', ltrim($value, '\\'));
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return var_export($value, true);
    }
}
