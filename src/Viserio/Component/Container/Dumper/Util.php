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

namespace Viserio\Component\Container\Dumper;

use Viserio\Contract\Container\Exception\RuntimeException;

class Util
{
    /**
     * Removes comments from a PHP source string.
     *
     * We don't use the PHP php_strip_whitespace() function
     * as we want the content to be readable and well-formatted.
     *
     * @param string $source A PHP string
     *
     * @return string The PHP string with the comments removed
     */
    public static function stripComments($source): string
    {
        if (! \function_exists('token_get_all')) {
            return $source;
        }

        $rawChunk = '';
        $output = '';
        $tokens = \token_get_all($source);
        $ignoreSpace = false;

        for ($i = 0; isset($tokens[$i]); $i++) {
            $token = $tokens[$i];

            if (! isset($token[1]) || 'b"' === $token) {
                $rawChunk .= $token;
            } elseif (\T_START_HEREDOC === $token[0]) {
                $output .= $rawChunk . $token[1];

                do {
                    $token = $tokens[++$i];
                    $output .= isset($token[1]) && 'b"' !== $token ? $token[1] : $token;
                } while (\T_END_HEREDOC !== $token[0]);

                $rawChunk = '';
            } elseif (\T_WHITESPACE === $token[0]) {
                if ($ignoreSpace) {
                    $ignoreSpace = false;

                    continue;
                }

                // replace multiple new lines with a single newline
                $rawChunk .= \preg_replace(['/\n{2,}/S'], "\n", $token[1]);
            } elseif (\in_array($token[0], [\T_COMMENT, \T_DOC_COMMENT], true)) {
                $ignoreSpace = true;
            } else {
                $rawChunk .= $token[1];
                // The PHP-open tag already has a new-line
                if (\T_OPEN_TAG === $token[0]) {
                    $ignoreSpace = true;
                }
            }
        }

        $output .= $rawChunk;

        unset($tokens, $rawChunk);

        \gc_mem_caches();

        return $output;
    }

    /**
     * Check if file exists or is empty.
     *
     * @param string $filename
     *
     * @throws \Viserio\Contract\Container\Exception\RuntimeException
     *
     * @return void
     */
    public static function checkFile(string $filename): void
    {
        if ($filename === '') {
            throw new RuntimeException('Filename was empty.');
        }

        if (! \file_exists($filename)) {
            throw new RuntimeException('File does not exist.');
        }

        if (! \is_readable($filename)) {
            throw new RuntimeException('File is not readable.');
        }

        if (! \is_file($filename)) {
            throw new RuntimeException("Is not a file: {$filename}");
        }
    }
}
