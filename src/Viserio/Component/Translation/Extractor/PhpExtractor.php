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

namespace Viserio\Component\Translation\Extractor;

use ArrayIterator;
use Iterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Viserio\Component\Translation\Extractor\PhpParser\ScalarString;
use Viserio\Contract\Translation\Exception\InvalidArgumentException;

class PhpExtractor extends AbstractFileExtractor
{
    public const MESSAGE_TOKEN = 300;
    public const METHOD_ARGUMENTS_TOKEN = 1000;
    public const DOMAIN_TOKEN = 1001;

    /**
     * Default domain for found messages.
     *
     * @var string
     */
    protected $defaultDomain = 'messages';

    /**
     * The sequence that captures translation messages.
     *
     * @var array
     */
    protected $sequences = [
        [
            '->',
            'trans',
            '(',
            self::MESSAGE_TOKEN,
            ',',
            self::METHOD_ARGUMENTS_TOKEN,
            ',',
            self::DOMAIN_TOKEN,
        ],
        [
            '->',
            'trans',
            '(',
            self::MESSAGE_TOKEN,
        ],
    ];

    /**
     * {@inheritdoc}
     *
     * @param mixed $resource
     */
    public function extract($resource): array
    {
        if (! \is_string($resource) && ! \is_array($resource)) {
            throw new InvalidArgumentException(\sprintf('The resource parameter must be of type string or array, [%s] given.', \is_object($resource) ? \get_class($resource) : \gettype($resource)));
        }

        $messages = [];
        $files = $this->extractFiles($resource);

        foreach ($files as $file) {
            $tokens = \token_get_all(\file_get_contents($file));

            foreach ($this->parseTokens($tokens) as $k => $v) {
                $messages[$k] = $v;
            }

            // PHP 7 memory manager will not release after token_get_all(), see https://bugs.php.net/70098
            unset($tokens);
            \gc_mem_caches();
        }

        return $messages;
    }

    /**
     * Normalizes a token.
     *
     * @param mixed $token
     *
     * @return string
     */
    protected function normalizeToken($token): string
    {
        if ($token !== 'b"' && isset($token[1])) {
            return $token[1];
        }

        return $token;
    }

    /**
     * Extracts trans message from PHP tokens.
     *
     * @param array $tokens
     *
     * @return array
     */
    protected function parseTokens(array $tokens): array
    {
        $tokenIterator = new ArrayIterator($tokens);
        $messages = [];

        for ($key = 0; $key < $tokenIterator->count(); $key++) {
            foreach ($this->sequences as $sequence) {
                $message = '';
                $domain = $this->defaultDomain;
                $tokenIterator->seek($key);

                foreach ($sequence as $sequenceKey => $item) {
                    $this->seekToNextRelevantToken($tokenIterator);

                    if ($this->normalizeToken($tokenIterator->current()) === $item) {
                        $tokenIterator->next();

                        continue;
                    }

                    if (self::MESSAGE_TOKEN === $item) {
                        $message = $this->getValue($tokenIterator);

                        if (\count($sequence) === ($sequenceKey + 1)) {
                            break;
                        }
                    } elseif (self::METHOD_ARGUMENTS_TOKEN === $item) {
                        $this->skipMethodArgument($tokenIterator);
                    } elseif (self::DOMAIN_TOKEN === $item) {
                        $domain = $this->getValue($tokenIterator);

                        break;
                    } else {
                        break;
                    }
                }

                if ($message) {
                    $messages[$domain][\trim($message)] = $this->prefix . \trim($message);

                    break;
                }
            }
        }

        return $messages;
    }

    /**
     * @param string $file
     *
     * @throws \InvalidArgumentException
     *
     * @return bool
     */
    protected function canBeExtracted($file): bool
    {
        return $this->isFile($file) && $this->isPhpFile($file);
    }

    /**
     * @param array|string $directory
     *
     * @return array
     */
    protected function extractFromDirectory($directory): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        $files = [];

        foreach ($iterator as $file) {
            if ($this->isPhpFile($file->getPathname())) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    /**
     * Seeks to a non-whitespace token.
     *
     * @param \Iterator $tokenIterator
     *
     * @return void
     */
    private function seekToNextRelevantToken(Iterator $tokenIterator): void
    {
        for (; $tokenIterator->valid(); $tokenIterator->next()) {
            $token = $tokenIterator->current();

            if ($token[0] !== \T_WHITESPACE) {
                break;
            }
        }
    }

    /**
     * @param \Iterator $tokenIterator
     *
     * @return void
     */
    private function skipMethodArgument(Iterator $tokenIterator): void
    {
        $openBraces = 0;

        for (; $tokenIterator->valid(); $tokenIterator->next()) {
            $token = $tokenIterator->current();

            if ($token[0] === '[' || $token[0] === '(') {
                $openBraces++;
            }

            if ($token[0] === ']' || $token[0] === ')') {
                $openBraces--;
            }

            if (($openBraces === 0 && $token[0] === ',') || ($openBraces === -1 && $token[0] === ')')) {
                break;
            }
        }
    }

    /**
     * Extracts the message from the iterator while the tokens
     * match allowed message tokens.
     *
     * @param \Iterator $tokenIterator
     *
     * @return string
     */
    private function getValue(Iterator $tokenIterator): string
    {
        $message = '';
        $docToken = '';

        for (; $tokenIterator->valid(); $tokenIterator->next()) {
            $t = $tokenIterator->current();

            if (! isset($t[1])) {
                break;
            }

            switch ($t[0]) {
                case \T_START_HEREDOC:
                    $docToken = $t[1];

                    break;
                case \T_ENCAPSED_AND_WHITESPACE:
                case \T_CONSTANT_ENCAPSED_STRING:
                    $message .= $t[1];

                    break;
                case \T_END_HEREDOC:
                    return ScalarString::parseDocString($docToken, $message);

                default:
                    break 2;
            }
        }

        if ($message) {
            $message = ScalarString::parse($message);
        }

        return $message;
    }

    /**
     * Check if file is a php file.
     *
     * @param string $file
     *
     * @return bool
     */
    private function isPhpFile(string $file): bool
    {
        return \pathinfo($file, \PATHINFO_EXTENSION) === 'php';
    }
}
