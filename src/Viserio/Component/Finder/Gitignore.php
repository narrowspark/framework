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

namespace Viserio\Component\Finder;

use Throwable;

/**
 * Gitignore matches against text.
 *
 * @author Ahmed Abdou <mail@ahmd.io>
 */
final class Gitignore
{
    /**
     * Last catch error message.
     *
     * @var null|string
     */
    private static $lastError;

    /**
     * Last catch error type.
     *
     * @var null|int
     */
    private static $lastType;

    /**
     * Returns a regexp which is the equivalent of the gitignore pattern.
     *
     * @return string The regexp
     */
    public static function toRegex(string $gitignoreFileContent): string
    {
        /** @var string $gitignoreFileContent */
        $gitignoreFileContent = self::box('\preg_replace', '/^[^\\\r\n]*#.*/m', '', $gitignoreFileContent);

        /** @var string[] $gitignoreLines */
        $gitignoreLines = self::box('\preg_split', '/\r\n|\r|\n/', $gitignoreFileContent);
        $gitignoreLines = \array_map('trim', $gitignoreLines);
        $gitignoreLines = \array_filter($gitignoreLines);

        $ignoreLinesPositive = \array_filter($gitignoreLines, static function (string $line): bool {
            return \preg_match('/^!/', $line) !== 1;
        });

        $ignoreLinesNegative = \array_filter($gitignoreLines, static function (string $line): bool {
            return \preg_match('/^!/', $line) === 1;
        });

        $ignoreLinesNegative = \array_map(static function (string $line): ?string {
            return \preg_replace('/^!(.*)/', '${1}', $line);
        }, $ignoreLinesNegative);

        $ignoreLinesNegative = \array_map([__CLASS__, 'getRegexFromGitignore'], $ignoreLinesNegative);
        $ignoreLinesPositive = \array_map([__CLASS__, 'getRegexFromGitignore'], $ignoreLinesPositive);

        if (\count($ignoreLinesPositive) === 0) {
            return '/^$/';
        }

        if (\count($ignoreLinesNegative) === 0) {
            return \sprintf('/%s/', \implode('|', $ignoreLinesPositive));
        }

        return \sprintf('/(?=^(?:(?!(%s)).)*$)(%s)/', \implode('|', $ignoreLinesNegative), \implode('|', $ignoreLinesPositive));
    }

    /**
     * @internal
     *
     * @return bool;
     */
    public static function handleError(int $type, string $msg): bool
    {
        self::$lastError = $msg;
        self::$lastType = $type;

        return true;
    }

    private static function getRegexFromGitignore(string $gitignorePattern): string
    {
        $regex = '(';

        if (\strpos($gitignorePattern, '/') === 0) {
            $gitignorePattern = \substr($gitignorePattern, 1);
            $regex .= '^';
        } else {
            $regex .= '(^|\/)';
        }

        if ($gitignorePattern[\strlen($gitignorePattern) - 1] === '/') {
            $gitignorePattern = \substr($gitignorePattern, 0, -1);
        }

        $iMax = \strlen($gitignorePattern);

        for ($i = 0; $i < $iMax; $i++) {
            $doubleChars = \substr($gitignorePattern, $i, 2);

            if ('**' === $doubleChars) {
                $regex .= '.+';
                $i++;

                continue;
            }

            $c = $gitignorePattern[$i];

            switch ($c) {
                case '*':
                    $regex .= '[^\/]+';

                    break;
                case '/':
                case '.':
                case ':':
                case '(':
                case ')':
                case '{':
                case '}':
                    $regex .= '\\' . $c;

                    break;

                default:
                    $regex .= $c;
            }
        }

        return $regex . '($|\/))';
    }

    /**
     * @codeCoverageIgnore
     *
     * Call the given callable with given args, but throws an ErrorException when an error/warning/notice is triggered.
     *
     * @throws Throwable
     */
    private static function box(callable $func)
    {
        self::$lastError = null;
        self::$lastType = null;

        \set_error_handler(__CLASS__ . '::handleError', \E_ALL & ~\E_DEPRECATED & ~\E_USER_DEPRECATED);

        try {
            $result = $func(...\array_slice(\func_get_args(), 1));

            \restore_error_handler();

            return $result;
        } catch (Throwable $e) {
            // @ignoreException
        }

        \restore_error_handler();

        throw $e;
    }
}
