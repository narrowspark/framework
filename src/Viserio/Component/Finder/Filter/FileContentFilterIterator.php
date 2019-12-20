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

namespace Viserio\Component\Finder\Filter;

/**
 * FileContentFilterIteratorAbstract filters files by their contents using patterns (regexps or strings).
 *
 * @author Fabien Potencier  <fabien@symfony.com>
 * @author Włodzimierz Gajda <gajdaw@gajdaw.pl>
 */
class FileContentFilterIterator extends AbstractMultiplePcreFilterIterator
{
    /**
     * Filters the iterator values.
     *
     * @return bool true if the value should be kept, false otherwise
     */
    public function accept()
    {
        if (! $this->matchRegexps && ! $this->noMatchRegexps) {
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
     * Converts string to regexp if necessary.
     *
     * @param string $string
     * @param string $str    Pattern: string or regexp
     *
     * @return string regexp corresponding to a given string or regexp
     */
    protected function toRegex(string $string): string
    {
        return $this->isRegex($string) ? $string : '/' . \preg_quote($string, '/') . '/';
    }
}
