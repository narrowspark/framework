<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Dumper;

use Viserio\Component\Contract\Parser\Dumper as DumperContract;
use Viserio\Component\Contract\Parser\Exception\DumpException;
use Yosymfony\Toml\Exception\DumpException as YosymfonyDumpException;
use Yosymfony\Toml\TomlBuilder;

class TomlDumper implements DumperContract
{
    /**
     * {@inheritdoc}
     *
     *    array[]
     */
    public function dump(array $data): string
    {
        try {
            $builder = $this->fromArray($data, new TomlBuilder());
        } catch (YosymfonyDumpException $exception) {
            throw new DumpException(
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getFile()
            );
        }

        return $builder->getTomlString();
    }

    /**
     * Build toml file from given array.
     *
     * @param array                       $data
     * @param \Yosymfony\Toml\TomlBuilder $builder
     * @param string                      $parent
     *
     * @return \Yosymfony\Toml\TomlBuilder
     */
    private function fromArray(array $data, TomlBuilder $builder, string $parent = ''): TomlBuilder
    {
        foreach ($data as $key => $value) {
            if (\is_array($value)) {
                if ($this->hasStringKeys($value)) {
                    $key = $parent !== '' ? "$parent.$key" : $key;

                    if (mb_strpos($key, '.') !== false) {
                        $builder->addTable($key);
                    }

                    $builder = $this->fromArray($value, $builder, $key);
                } elseif ($this->onlyArrays($value)) {
                    $builder = $this->processArrayOfArrays($value, $key, $builder);
                } else {
                    // Plain array.
                    $builder->addValue($key, $value);
                }
            } else {
                // Simple key/value.
                $builder->addValue($key, $value);
            }
        }

        return $builder;
    }

    /**
     * @param array                       $values
     * @param string                      $parent
     * @param \Yosymfony\Toml\TomlBuilder $builder
     *
     * @return \Yosymfony\Toml\TomlBuilder
     */
    private function processArrayOfArrays(array $values, string $parent, TomlBuilder $builder): TomlBuilder
    {
        $array = [];

        foreach ($values as $value) {
            if ($this->hasStringKeys($value)) {
                $builder->addArrayTables($parent);

                foreach ($value as $key => $val) {
                    if (\is_array($val)) {
                        $builder = $this->processArrayOfArrays($val, "$parent.$key", $builder);
                    } else {
                        $builder->addValue((string) $key, $val);
                    }
                }
            } else {
                $array[] = $value;
            }
        }

        if (\count($array) !== 0) {
            $builder->addValue($parent, $array);
        }

        return $builder;
    }

    /**
     * @param array $array
     *
     * @return bool
     */
    private function hasStringKeys(array $array): bool
    {
        return \count(\array_filter(\array_keys($array), '\is_string')) > 0;
    }

    /**
     * @param array $array
     *
     * @return bool
     */
    private function onlyArrays(array $array): bool
    {
        return \count(\array_filter(\array_values($array), '\is_array')) === \count($array);
    }
}
