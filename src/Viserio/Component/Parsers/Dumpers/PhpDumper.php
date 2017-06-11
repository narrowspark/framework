<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Dumpers;

use Viserio\Component\Contracts\Parsers\Dumper as DumperContract;

class PhpDumper implements DumperContract
{
    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        $output = '<?php
declare(strict_types=1);

return ' . $this->prepareArray($data) . ';';

        return $output;
    }

    /**
     * Prepare array for save.
     *
     * @param iterable $config
     * @param in       $indentLevel
     *
     * @return string
     */
    private function prepareArray(array $config, int $indentLevel = 1): string
    {
        $indent  = str_repeat(' ', $indentLevel * 4);
        $entries = [];

        foreach ($config as $key => $value) {
            if (! is_int($key)) {
                if (is_string($key) && class_exists($key) && ctype_upper($key[0])) {
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
     * @return string|int|float
     */
    private function createValue($value, int $indentLevel)
    {
        if (is_array($value)) {
            return $this->prepareArray($value, $indentLevel + 1);
        }

        if (is_string($value) && class_exists($value) && ctype_upper($value[0])) {
            return sprintf('\\%s::class', ltrim($value, '\\'));
        }

        if (is_numeric($value)) {
            return $value + 0;
        }

        return var_export($value, true);
    }
}
