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

namespace Viserio\Component\Finder\Filter;

/**
 * FileContentFilterIteratorAbstract filters files by their contents using patterns (regexps or strings).
 *
 * Based on the symfony finder package
 *
 * @see https://github.com/symfony/symfony/blob/5.0/src/Symfony/Component/Finder/Iterator/FileContentFilterIterator.php
 *
 * @author Fabien Potencier  <fabien@symfony.com>
 * @author Włodzimierz Gajda <gajdaw@gajdaw.pl>
 */
class FileContentFilterIterator extends AbstractMultiplePcreFilterIterator
{
    /**
     * {@inheritdoc}
     */
    public function accept(): bool
    {
        if (\count($this->matchRegexps) === 0 && \count($this->noMatchRegexps) === 0) {
            return true;
        }

        $fileinfo = $this->current();

        if ($fileinfo->isDir() || ! $fileinfo->isReadable()) {
            return false;
        }

        $content = $fileinfo->getContents();

        if (! $content) {
            return false;
        }

        return $this->isAccepted($content);
    }

    /**
     * {@inheritdoc}
     */
    protected function toRegex(string $string): string
    {
        return $this->isRegex($string) ? $string : '/' . \preg_quote($string, '/') . '/';
    }
}
