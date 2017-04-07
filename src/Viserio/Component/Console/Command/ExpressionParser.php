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
        preg_match_all('/^[^\s]*|(\[\s*(.*?)\]|[[:word:]]+\=\*|[[:word:]]+\=|[[:word:]]+|-+[[:word:]]+)/', $expression, $matches);

        if (trim($expression) === '') {
            throw new InvalidCommandExpression('The expression was empty.');
        }

        $tokens    = array_values(array_filter(array_map('trim', $matches[0])));
        $name      = array_shift($tokens);
        $arguments = [];
        $options   = [];

        foreach ($tokens as $token) {
            if (self::startsWith($token, '--')) {
                throw new InvalidCommandExpression('An option must be enclosed by brackets: [--option]');
            }

            if (self::isOption($token)) {
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
     * Check if token is a option.
     *
     * @param string $token
     *
     * @return bool
     */
    protected static function isOption(string $token): bool
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

        switch (true) {
            case self::endsWith($token, '=*]'):
                return new InputArgument(trim($token, '[=*]'), InputArgument::IS_ARRAY, $description);
            case self::endsWith($token, '=*'):
                return new InputArgument(trim($token, '=*'), InputArgument::IS_ARRAY | InputArgument::REQUIRED, $description);
            case preg_match('/\[(.+)\=\*(.+)\]/', $token, $matches):
                return new InputArgument($matches[1], InputArgument::IS_ARRAY | InputArgument::REQUIRED, $description, $matches[2]);
            case preg_match('/\[(.+)\=(.+)\]/', $token, $matches):
                return new InputArgument($matches[1], InputArgument::OPTIONAL, $description, $matches[2]);
            case self::startsWith($token, '[') && self::endsWith($token, ']'):
                return new InputArgument(trim($token, '[]'), InputArgument::OPTIONAL, $description);
            default:
                return new InputArgument($token, InputArgument::REQUIRED, $description);
        }
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

        switch (true) {
            case self::endsWith($token, '=*'):
                return new InputOption(rtrim($name, '=*'), $shortcut, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, $description);
            case self::endsWith($token, '='):
                return new InputOption(rtrim($name, '='), $shortcut, InputOption::VALUE_REQUIRED, $description);
            case preg_match('/(.+)\=\*(.+)/', $token, $matches):
                return new InputOption($matches[1], $shortcut, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, $description, $matches[2]);
            case preg_match('/(.+)\=(.+)/', $token, $matches):
                return new InputOption($matches[1], $shortcut, InputOption::VALUE_OPTIONAL, $description, $matches[2]);
            default:
                return new InputOption($token, $shortcut, InputOption::VALUE_NONE, $description);
        }
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
