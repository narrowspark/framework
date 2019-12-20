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

namespace Viserio\Component\Finder;

/**
 * Gitignore matches against text.
 *
 * @author Ahmed Abdou <mail@ahmd.io>
 */
final class Gitignore
{
    /**
     * Returns a regexp which is the equivalent of the gitignore pattern.
     *
     * @param string $gitignoreFileContent
     *
     * @return string The regexp
     */
    public static function toRegex(string $gitignoreFileContent): string
    {
        $gitignoreFileContent = \preg_replace('/^[^\\\r\n]*#.*/m', '', $gitignoreFileContent);

        $gitignoreLines = \preg_split('/\r\n|\r|\n/', $gitignoreFileContent);
        $gitignoreLines = \array_map('trim', $gitignoreLines);
        $gitignoreLines = \array_filter($gitignoreLines);

        $ignoreLinesPositive = \array_filter($gitignoreLines, static function (string $line) {
            return ! \preg_match('/^!/', $line);
        });

        $ignoreLinesNegative = \array_filter($gitignoreLines, static function (string $line) {
            return \preg_match('/^!/', $line);
        });

        $ignoreLinesNegative = \array_map(static function (string $line) {
            return \preg_replace('/^!(.*)/', '${1}', $line);
        }, $ignoreLinesNegative);

        $ignoreLinesNegative = \array_map([__CLASS__, 'getRegexFromGitignore'], $ignoreLinesNegative);
        $ignoreLinesPositive = \array_map([__CLASS__, 'getRegexFromGitignore'], $ignoreLinesPositive);

        if (empty($ignoreLinesPositive)) {
            return '/^$/';
        }

        if (empty($ignoreLinesNegative)) {
            return \sprintf('/%s/', \implode('|', $ignoreLinesPositive));
        }

        return \sprintf('/(?=^(?:(?!(%s)).)*$)(%s)/', \implode('|', $ignoreLinesNegative), \implode('|', $ignoreLinesPositive));
    }

    /**
     * @param string $gitignorePattern
     *
     * @return string
     */
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

        $regex .= '($|\/)';
        $regex .= ')';

        return $regex;
    }
}
