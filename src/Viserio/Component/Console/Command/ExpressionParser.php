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
        preg_match_all('/^[^\s]*\s|\[\s*(.*?)\]/', $expression, $tokens);

        if (count($tokens[0]) === 0) {
            throw new InvalidCommandExpression('The expression was empty');
        }

        $tokens = array_map('trim', $tokens[0]);
        $tokens = array_values(array_filter($tokens));

        $name      = array_shift($tokens);
        $arguments = [];
        $options   = [];

        foreach ($tokens as $token) {
            if (self::startsWith($token, '--')) {
                throw new InvalidCommandExpression('An option must be enclosed by brackets: [--option]');
            }

            if ($this->isOption($token)) {
                $options[] = self::parseOption($token);
            } else {
                $arguments[] = self::parseArgument($token);
            }
        }

        return [
            'name'      => $name,
            'arguments' => $arguments,
            'options'   => $options,
        ];
    }

    /**
     * Extract the name of the command from the expression.
     *
     * @param string $expression
     *
     * @return string
     */
    protected static function getName(string $expression): string
    {
        if (trim($expression) === '') {
            throw new InvalidCommandExpression('Console command definition is empty.');
        }

        if (! preg_match('/[^\s]+/', $expression, $matches)) {
            throw new InvalidCommandExpression('Unable to determine command name from signature.');
        }

        return $matches[0];
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
    protected static function parseArgument(string $token): InputArgument
    {
        list($token, $description) = static::extractDescription($token);

        $default = null;

        if (self::endsWith($token, ']*')) {
            $mode = InputArgument::IS_ARRAY;

            if (preg_match('/\[(.+)\=\*(.+)\]*/', $token, $matches)) {
                $token   = $matches[1];
                $default = $matches[2];
            }

            $name = trim($token, '[]*');
        } elseif (self::endsWith($token, '*')) {
            $mode = InputArgument::IS_ARRAY | InputArgument::REQUIRED;
            $name = trim($token, '*');
        } elseif (self::startsWith($token, '[')) {
            $mode = InputArgument::OPTIONAL;

            if (preg_match('/\[(.+)\=(.+)\]/', $token, $matches)) {
                $token   = $matches[1];
                $default = $matches[2];
            }

            $name = trim($token, '[]');
        } else {
            $mode = InputArgument::REQUIRED;
            $name = $token;
        }

        return new InputArgument($name, $mode, $description, $default);
    }

    /**
     * Parse options.
     *
     * @param string $token
     *
     * @return \Viserio\Component\Console\Input\InputOption
     */
    protected static function parseOption(string $token): InputOption
    {
        list($token, $description) = static::extractDescription(trim($token, '[]'));

        // Shortcut [-y|--yell]
        if (mb_strpos($token, '|') !== false) {
            list($shortcut, $token) = explode('|', $token, 2);
            $shortcut               = ltrim($shortcut, '-');
        } else {
            $shortcut = null;
        }

        $name    = ltrim($token, '-');
        $default = null;

        if (self::endsWith($token, '=]*')) {
            $mode = InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY;
            $name = mb_substr($name, 0, -3);
        } elseif (self::endsWith($token, '=')) {
            $mode = InputOption::VALUE_REQUIRED;
            $name = rtrim($name, '=');
        } elseif (preg_match('/(.+)\=\*(.+)/', $name, $matches)) {
            $mode    = InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY;
            $name    = $matches[1];
            $default = $matches[2];
        } elseif (preg_match('/(.+)\=(.+)/', $name, $matches)) {
            $mode    = InputOption::VALUE_OPTIONAL;
            $name    = $matches[1];
            $default = $matches[2];
        } else {
            $mode = InputOption::VALUE_NONE;
        }

        return new InputOption($name, $shortcut, $mode, $description, $default);
    }

    /**
     * Parse the token into its token and description segments.
     *
     * @param string $token
     *
     * @return array
     */
    protected static function extractDescription(string $token): array
    {
        preg_match('/(.*)\s:(\s+.*(?<!]))(.*)/', trim($token), $parts);

        return count($parts) === 4 ? [$parts[1] . $parts[3], trim($parts[2])] : [$token, ''];
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    private static function startsWith(string $haystack, string $needle): bool
    {
        if ($needle != '' && mb_substr($haystack, 0, mb_strlen($needle)) === $needle) {
            return true;
        }

        return false;
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    private static function endsWith(string $haystack, string $needle): bool
    {
        if (mb_substr($haystack, -mb_strlen($needle)) === $needle) {
            return true;
        }

        return false;
    }
}
