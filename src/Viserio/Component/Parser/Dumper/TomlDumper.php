<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Parser\Dumper;

use Viserio\Contract\Parser\Dumper as DumperContract;
use Viserio\Contract\Parser\Exception\DumpException;
use Yosymfony\Toml\Exception\DumpException as YosymfonyDumpException;
use Yosymfony\Toml\TomlBuilder;

class TomlDumper implements DumperContract
{
    /**
     * {@inheritdoc}
     *
     * @example
     *     array[]
     *        array['key']
     *            ['key']                string|int|array|\Datetime
     *            or
     *        array['key']
     *            array[]
     *                ['key']            string|int|array|\Datetime
     *                array['key']
     *                    array[]
     *                        ['key']    string|int|array|\Datetime
     */
    public function dump(array $data): string
    {
        try {
            $builder = $this->fromArray($data, new TomlBuilder());
        } catch (YosymfonyDumpException $exception) {
            throw new DumpException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return $builder->getTomlString();
    }

    /**
     * Build toml file from given array.
     *
     * @param array<int|string, mixed> $data
     */
    private function fromArray(array $data, TomlBuilder $builder, string $parent = ''): TomlBuilder
    {
        foreach ($data as $key => $value) {
            $key = (string) $key;

            if (\is_array($value)) {
                if ($this->hasStringKeys($value)) {
                    $key = $parent !== '' ? "{$parent}.{$key}" : $key;

                    if (\strpos($key, '.') !== false) {
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
     * Run through all arrays.
     *
     * @param array<int|string, mixed> $values
     */
    private function processArrayOfArrays(array $values, string $parent, TomlBuilder $builder): TomlBuilder
    {
        $array = [];

        foreach ($values as $value) {
            if ($this->hasStringKeys($value)) {
                $builder->addArrayOfTable($parent);

                foreach ($value as $key => $val) {
                    if (\is_array($val)) {
                        $builder = $this->processArrayOfArrays($val, "{$parent}.{$key}", $builder);
                    } else {
                        $builder->addValue($key, $val);
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
     * Check if array has string keys.
     *
     * @param array<int|string, mixed> $array
     */
    private function hasStringKeys(array $array): bool
    {
        return \count(\array_filter(\array_keys($array), '\is_string')) > 0;
    }

    /**
     * Check if array has only arrays.
     *
     * @param array<int|string, mixed> $array
     */
    private function onlyArrays(array $array): bool
    {
        return \count(\array_filter(\array_values($array), '\is_array')) === \count($array);
    }
}
