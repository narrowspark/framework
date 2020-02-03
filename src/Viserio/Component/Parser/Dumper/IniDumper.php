<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Parser\Dumper;

use Viserio\Contract\Parser\Dumper as DumperContract;
use Viserio\Contract\Parser\Exception\RuntimeException;

class IniDumper implements DumperContract
{
    /**
     * If true the INI string is rendered in the global namespace without
     * sections.
     *
     * @var bool
     */
    protected $renderWithoutSections = false;

    /**
     * Separator for nesting levels of configuration data identifiers.
     *
     * @var string
     */
    private $nestSeparator = '.';

    /**
     * Get nest separator.
     *
     * @return string
     */
    public function getNestSeparator(): string
    {
        return $this->nestSeparator;
    }

    /**
     * Set nest separator.
     *
     * @param string $separator
     *
     * @return self
     */
    public function setNestSeparator(string $separator): self
    {
        $this->nestSeparator = $separator;

        return $this;
    }

    /**
     * Set if rendering should occur without sections or not.
     *
     * If set to true, the INI file is rendered without sections completely
     * into the global namespace of the INI file.
     *
     * @param bool $withoutSections
     *
     * @return self
     */
    public function setRenderWithoutSectionsFlags(bool $withoutSections): self
    {
        $this->renderWithoutSections = $withoutSections;

        return $this;
    }

    /**
     * Return whether the writer should render without sections.
     *
     * @return bool
     */
    public function shouldRenderWithoutSections(): bool
    {
        return $this->renderWithoutSections;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        $output = '';

        if ($this->shouldRenderWithoutSections()) {
            $output .= $this->addBranch($data);
        } else {
            $data = $this->sortRootElements($data);

            foreach ($data as $sectionName => $config) {
                if (! \is_array($config)) {
                    $output .= $sectionName
                        . ' = '
                        . $this->prepareValue($config)
                        . "\n";
                } else {
                    $output .= '[' . $sectionName . "]\n"
                        . $this->addBranch($config)
                        . "\n";
                }
            }
        }


        return $output;
    }

    /**
     * Add a branch to an INI string recursively.
     *
     * @param array<int|string, mixed>            $config
     * @param array<int|string, float|int|string> $parents
     *
     * @return string
     */
    protected function addBranch(array $config, array $parents = []): string
    {
        $iniString = '';

        foreach ($config as $key => $value) {
            $group = \array_merge($parents, [$key]);

            if (\is_array($value)) {
                $iniString .= $this->addBranch($value, $group);
            } else {
                $iniString .= \implode($this->getNestSeparator(), $group)
                    . ' = '
                    . $this->prepareValue($value)
                    . "\n";
            }
        }

        return $iniString;
    }

    /**
     * Root elements that are not assigned to any section needs to be on the
     * top of config.
     *
     * @param array<int|string, mixed> $config
     *
     * @return array<int|string, mixed>
     */
    protected function sortRootElements(array $config): array
    {
        $sections = [];
        // Remove sections from config array.
        foreach ($config as $key => $value) {
            if (\is_array($value)) {
                $sections[$key] = $value;

                unset($config[$key]);
            }
        }

        // Read sections to the end.
        foreach ($sections as $key => $value) {
            $config[$key] = $value;
        }

        return $config;
    }

    /**
     * Converts the supplied value into a valid ini representation.
     *
     * @param mixed $value
     *
     * @throws \Viserio\Contract\Parser\Exception\RuntimeException
     *
     * @return float|int|string
     */
    private function prepareValue($value)
    {
        if ($value === null) {
            return 'null';
        }

        if (\is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (\is_int($value) || \is_float($value)) {
            return $value;
        }

        if (\strpos($value, '"') === false) {
            return '"' . $value . '"';
        }

        throw new RuntimeException('Value can not contain double quotes.');
    }
}
