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

use Viserio\Component\Finder\Util;

/**
 * FilenameFilterIterator filters files by patterns (a regexp, a glob, or a string).
 */
class FilenameFilterIterator extends AbstractMultiplePcreFilterIterator
{
    /**
     * {@inheritdoc}
     */
    public function accept(): bool
    {
        return $this->isAccepted($this->current()->getFilename());
    }

    /**
     * Converts glob to regexp.
     *
     * PCRE patterns are left unchanged.
     * Glob strings are transformed with Util::toRegex().
     *
     * @param string $string Pattern: glob or regexp
     *
     * @return string regexp corresponding to a given glob or regexp
     */
    protected function toRegex(string $string): string
    {
        return $this->isRegex($string) ? $string : Util::toRegEx($string, '~', false);
    }
}
