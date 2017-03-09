<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Command;

use Viserio\Component\Console\Input\InputArgument;
use Viserio\Component\Console\Input\InputOption;
use Viserio\Component\Contracts\Console\Exceptions\InvalidCommandExpression;

class ExpressionParser
{
    /**
     * @param string $expression
     *
     * @return array
     */
    public function parse(string $expression): array
    {
        $tokens = explode(' ', $expression);
        $tokens = array_map('trim', $tokens);
        $tokens = array_values(array_filter($tokens));

        if (count($tokens) === 0) {
            throw new InvalidCommandExpression('The expression was empty');
        }

        $name      = array_shift($tokens);
        $arguments = [];
        $options   = [];

        foreach ($tokens as $token) {
            if (self::startsWith($token, '--')) {
                throw new InvalidCommandExpression('An option must be enclosed by brackets: [--option]');
            }

            if ($this->isOption($token)) {
                $options[] = $this->parseOption($token);
            } else {
                $arguments[] = $this->parseArgument($token);
            }
        }

        return [
            'name'      => $name,
            'arguments' => $arguments,
            'options'   => $options,
        ];
    }

    /**
     * Check if token is a option.
     *
     * @param string $token
     *
     * @return bool
     */
    protected function isOption(string $token): bool
    {
        return self::startsWith($token, '[-');
    }

    /**
     * Parse arguments.
     *
     * @param string $token
     *
     * @return \Viserio\Component\Console\Input\InputArgument
     */
    protected function parseArgument(string $token): InputArgument
    {
        if (self::endsWith($token, ']*')) {
            $mode = InputArgument::IS_ARRAY;
            $name = trim($token, '[]*');
        } elseif (self::endsWith($token, '*')) {
            $mode = InputArgument::IS_ARRAY | InputArgument::REQUIRED;
            $name = trim($token, '*');
        } elseif (self::startsWith($token, '[')) {
            $mode = InputArgument::OPTIONAL;
            $name = trim($token, '[]');
        } else {
            $mode = InputArgument::REQUIRED;
            $name = $token;
        }

        return new InputArgument($name, $mode);
    }

    /**
     * Parse options.
     *
     * @param string $token
     *
     * @return \Viserio\Component\Console\Input\InputOption
     */
    protected function parseOption(string $token): InputOption
    {
        $token = trim($token, '[]');

        // Shortcut [-y|--yell]
        if (mb_strpos($token, '|') !== false) {
            list($shortcut, $token) = explode('|', $token, 2);
            $shortcut               = ltrim($shortcut, '-');
        } else {
            $shortcut = null;
        }

        $name = ltrim($token, '-');

        if (self::endsWith($token, '=]*')) {
            $mode = InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY;
            $name = mb_substr($name, 0, -3);
        } elseif (self::endsWith($token, '=')) {
            $mode = InputOption::VALUE_REQUIRED;
            $name = rtrim($name, '=');
        } else {
            $mode = InputOption::VALUE_NONE;
        }

        return new InputOption($name, $shortcut, $mode);
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param string $haystack
     * @param string $needles
     *
     * @return bool
     */
    private static function startsWith(string $haystack, string $needle): bool
    {
        if ($needle != '' && substr($haystack, 0, strlen($needle)) === (string) $needle) {
            return true;
        }

        return false;
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param string $haystack
     * @param string $needles
     *
     * @return bool
     */
    private static function endsWith(string $haystack, string $needles): bool
    {
        if (substr($haystack, -strlen($needle)) === (string) $needle) {
            return true;
        }

        return false;
    }
}
