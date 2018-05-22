<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Util;

final class Strip
{
    /**
     * Private constructor; non-instantiable.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

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
    public static function stripComments(string $source): string
    {
        $rawChunk    = '';
        $output      = '';
        $tokens      = \token_get_all($source);
        $ignoreSpace = false;

        for ($i = 0; isset($tokens[$i]); $i++) {
            $token = $tokens[$i];

            if (! isset($token[1]) || 'b"' === $token) {
                $rawChunk .= $token;
            } elseif (T_START_HEREDOC === $token[0]) {
                $output .= $rawChunk . $token[1];

                do {
                    $token = $tokens[++$i];
                    $output .= isset($token[1]) && 'b"' !== $token ? $token[1] : $token;
                } while (T_END_HEREDOC !== $token[0]);

                $rawChunk = '';
            } elseif (T_WHITESPACE === $token[0]) {
                if ($ignoreSpace) {
                    $ignoreSpace = false;

                    continue;
                }

                // replace multiple new lines with a single newline
                $rawChunk .= \preg_replace(['/\n{2,}/S'], "\n", $token[1]);
            } elseif (\in_array($token[0], [T_COMMENT, T_DOC_COMMENT], true)) {
                $ignoreSpace = true;
            } else {
                $rawChunk .= $token[1];

                // The PHP-open tag already has a new-line
                if (T_OPEN_TAG === $token[0]) {
                    $ignoreSpace = true;
                }
            }
        }

        $output .= $rawChunk;

        // PHP 7 memory manager will not release after token_get_all(), see https://bugs.php.net/70098
        unset($tokens, $rawChunk);
        \gc_mem_caches();

        return $output;
    }
}
