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

namespace Viserio\Component\Parser\Parser;

use Viserio\Contract\Parser\Exception\ParseException;
use Viserio\Contract\Parser\Exception\RuntimeException;
use Viserio\Contract\Parser\Parser as ParserContract;

class IniParser implements ParserContract
{
    /**
     * Separator for nesting levels of configuration data identifiers.
     *
     * @var string
     */
    private $nestSeparator = '.';

    /**
     * Flag which determines whether sections are processed or not.
     *
     * @see https://www.php.net/parse_ini_file
     *
     * @var bool
     */
    protected $processSections = true;

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
     * Get if sections should be processed
     * When sections are not processed,section names are stripped and section
     * values are merged.
     *
     * @see https://www.php.net/parse_ini_file
     *
     * @return bool
     */
    public function getProcessSections(): bool
    {
        return $this->processSections;
    }

    /**
     * Marks whether sections should be processed.
     * When sections are not processed,section names are stripped and section
     * values are merged.
     *
     * @see https://www.php.net/parse_ini_file
     *
     * @param bool $processSections
     *
     * @return $this
     */
    public function setProcessSections(bool $processSections): self
    {
        $this->processSections = $processSections;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        \set_error_handler(static function ($severity, $message, $file, $line): void {
            throw new ParseException(['severity' => $severity, 'message' => $message, 'file' => $file, 'line' => $line]);
        });

        $ini = \parse_ini_string(\trim($payload), $this->getProcessSections(), \INI_SCANNER_RAW);

        \restore_error_handler();

        if (! $ini) {
            throw new ParseException(['message' => 'No parsable content.']);
        }

        return $this->process($ini);
    }

    /**
     * Process data from the parsed ini file.
     *
     * @param array $data
     *
     * @return array
     */
    protected function process(array $data): array
    {
        $config = [];

        foreach ($data as $section => $value) {
            if (\is_array($value)) {
                if (\strpos($section, $this->getNestSeparator()) !== false) {
                    $sections = \explode($this->getNestSeparator(), $section);
                    $config = \array_merge_recursive($config, $this->buildNestedSection($sections, $value));
                } else {
                    $config[$section] = $this->processSection($value);
                }
            } else {
                $this->processKey($section, $value, $config);
            }
        }

        return $config;
    }

    /**
     * Process a nested section.
     *
     * @param array $sections
     * @param mixed $value
     *
     * @return array
     */
    private function buildNestedSection(array $sections, $value): array
    {
        if (! $sections) {
            return $this->processSection($value);
        }

        $nestedSection = [];

        $first = \array_shift($sections);
        $nestedSection[$first] = $this->buildNestedSection($sections, $value);

        return $nestedSection;
    }

    /**
     * Process a section.
     *
     * @param array $section
     *
     * @return array
     */
    protected function processSection(array $section): array
    {
        $config = [];

        foreach ($section as $key => $value) {
            $this->processKey($key, $value, $config);
        }

        return $config;
    }

    /**
     * Process a key.
     *
     * @param string $key
     * @param string $value
     * @param array  $config
     *
     * @throws RuntimeException
     */
    protected function processKey($key, $value, array &$config): void
    {
        if (\is_string($key) && \strpos($key, $this->getNestSeparator()) !== false) {
            $pieces = \explode($this->getNestSeparator(), $key, 2);

            if ($pieces[0] === '' || $pieces[1] === '') {
                throw new RuntimeException(\sprintf('Invalid key "%s"', $key));
            }

            if (! isset($config[$pieces[0]])) {
                if ($pieces[0] === '0' && ! empty($config)) {
                    $config = [$pieces[0] => $config];
                } else {
                    $config[$pieces[0]] = [];
                }
            } elseif (! \is_array($config[$pieces[0]])) {
                throw new RuntimeException(\sprintf('Cannot create sub-key for "%s", as key already exists', $pieces[0]));
            }

            $this->processKey($pieces[1], $value, $config[$pieces[0]]);
        } else {
            $config[$key] = $this->normalize($value);
        }
    }

    /**
     * Normalizes INI and other values.
     *
     * @param mixed $value
     *
     * @return null|array|bool|int|string
     */
    private function normalize($value)
    {
        // Normalize array values
        if (\is_array($value)) {
            foreach ($value as &$subValue) {
                $subValue = self::normalize($subValue);
            }

            return $value;
        }

        // Don't normalize non-string value
        if (! \is_string($value)) {
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
        if (\is_numeric($value)) {
            $numericValue = $value + 0;

            if ((\is_int($numericValue) && (int) $value === $numericValue)
                || (\is_float($numericValue) && (float) $value === $numericValue)
            ) {
                $value = $numericValue;
            }
        } elseif (\is_string($value) && \preg_match('/^\'*.+\'$/m', $value) === 1) {
            $value = \ltrim(\rtrim($value, '\''), '\'');
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
            if (\strcasecmp($value, $comparison) === 0) {
                return true;
            }
        }

        return false;
    }
}
