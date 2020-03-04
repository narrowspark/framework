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

namespace Viserio\Component\Console\Command;

use Viserio\Component\Console\Input\InputArgument;
use Viserio\Component\Console\Input\InputOption;
use Viserio\Contract\Console\Exception\InvalidCommandExpression;

final class ExpressionParser
{
    /**
     * Parse given command string.
     *
     * @throws \Viserio\Contract\Console\Exception\InvalidCommandExpression
     */
    public static function parse(string $expression): array
    {
        \preg_match_all('/^\S*|(\[\s*(.*?)\]|[[:alnum:]_-]+\=\*|[[:alnum:]_-]+\?|[[:alnum:]_-]+|-+[[:alnum:]_\-=*]+)/', $expression, $matches);

        if (\trim($expression) === '') {
            throw new InvalidCommandExpression('The expression was empty.');
        }

        $tokens = \array_values(\array_filter(\array_map('trim', $matches[0])));
        $name = \array_shift($tokens);
        $arguments = [];
        $options = [];

        foreach ($tokens as $token) {
            if (self::startsWith($token, '--')) {
                throw new InvalidCommandExpression('An option must be enclosed by brackets: [--option].');
            }

            if (self::isOption($token)) {
                $options[] = self::parseOption($token);
            } else {
                $arguments[] = self::parseArgument($token);
            }
        }

        return [
            'name' => $name,
            'arguments' => $arguments,
            'options' => $options,
        ];
    }

    /**
     * Check if token is a option.
     */
    private static function isOption(string $token): bool
    {
        return self::startsWith($token, '[-');
    }

    /**
     * Parse arguments.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    private static function parseArgument(string $token): InputArgument
    {
        [$token, $description] = self::extractDescription($token);

        if (self::endsWith($token, '=*]')) {
            return new InputArgument(\trim($token, '[=*]'), InputArgument::IS_ARRAY, $description);
        }

        if (self::endsWith($token, '=*')) {
            return new InputArgument(\trim($token, '=*'), InputArgument::IS_ARRAY | InputArgument::REQUIRED, $description);
        }

        if (\preg_match('/(.*)\?$/', $token, $matches) === 1) {
            return new InputArgument($matches[1], InputArgument::OPTIONAL, $description);
        }

        if (\preg_match('/\[(.+)\?\]/', $token, $matches) === 1) {
            return new InputArgument($matches[1], InputArgument::OPTIONAL, $description);
        }

        if (\preg_match('/\[(.+)\=\*(.+)\]/', $token, $matches) === 1) {
            return new InputArgument($matches[1], InputArgument::IS_ARRAY, $description, \preg_split('/,\s?/', $matches[2]));
        }

        if (\preg_match('/\[(.+)\=(.+)\]/', $token, $matches) === 1) {
            return new InputArgument($matches[1], InputArgument::OPTIONAL, $description, $matches[2]);
        }

        if (self::startsWith($token, '[') && self::endsWith($token, ']')) {
            return new InputArgument(\trim($token, '[]'), InputArgument::OPTIONAL, $description);
        }

        return new InputArgument($token, InputArgument::REQUIRED, $description);
    }

    /**
     * Parse options.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    private static function parseOption(string $token): InputOption
    {
        [$token, $description] = self::extractDescription(\trim($token, '[]'));

        // Shortcut [-y|--yell]
        if (\strpos($token, '|') !== false) {
            [$shortcut, $token] = \explode('|', $token, 2);
            $shortcut = \ltrim($shortcut, '-');
        } else {
            $shortcut = null;
        }

        $name = \ltrim($token, '-');

        if (self::endsWith($token, '=*')) {
            return new InputOption(\rtrim($name, '=*'), $shortcut, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, $description);
        }

        if (self::endsWith($token, '=')) {
            return new InputOption(\rtrim($name, '='), $shortcut, InputOption::VALUE_REQUIRED, $description);
        }

        if (\preg_match('/(.+)\=\*(.+)/', $token, $matches) === 1) {
            return new InputOption($matches[1], $shortcut, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, $description, \preg_split('/,\s?/', $matches[2]));
        }

        if (\preg_match('/(.+)\=(.+)/', $token, $matches) === 1) {
            return new InputOption($matches[1], $shortcut, InputOption::VALUE_OPTIONAL, $description, $matches[2]);
        }

        return new InputOption($token, $shortcut, InputOption::VALUE_NONE, $description);
    }

    /**
     * Parse the token into its token and description segments.
     */
    private static function extractDescription(string $token): array
    {
        \preg_match('/(.*)\s:(\s+.*(?<!]))(.*)/', \trim($token), $parts);

        return \count($parts) === 4 ? [$parts[1] . $parts[3], \trim($parts[2])] : [$token, ''];
    }

    /**
     * Determine if a given string starts with a given substring.
     */
    private static function startsWith(string $haystack, string $needle): bool
    {
        return $needle !== '' && \strrpos($haystack, $needle) === 0;
    }

    /**
     * Determine if a given string ends with a given substring.
     */
    private static function endsWith(string $haystack, string $needle): bool
    {
        return \substr($haystack, -\strlen($needle)) === $needle;
    }
}
