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
 * PathFilterIterator filters files by path patterns (e.g. some/special/dir).
 *
 * Based on the symfony finder package
 *
 * @see https://github.com/symfony/symfony/blob/5.0/src/Symfony/Component/Finder/Iterator/PathFilterIterator.php
 *
 * @author Fabien Potencier  <fabien@symfony.com>
 * @author Włodzimierz Gajda <gajdaw@gajdaw.pl>
 */
class PathFilterIterator extends AbstractMultiplePcreFilterIterator
{
    /**
     * {@inheritdoc}
     */
    public function accept(): bool
    {
        /** @var \Viserio\Component\Finder\SplFileInfo $fileInfo */
        $fileInfo = $this->current();
        $filename = $fileInfo->getSubPathname();

        if (\PHP_OS_FAMILY === 'Windows') {
            $filename = \str_replace('\\', '/', $filename);
        }

        return $this->isAccepted($filename);
    }

    /**
     * Converts strings to regexp.
     *
     * PCRE patterns are left unchanged.
     *
     * Default conversion:
     *     'lorem/ipsum/dolor' ==>  'lorem\/ipsum\/dolor/'
     *
     * Use only / as directory separator (on Windows also).
     *
     * @param string $string Pattern: regexp or dirname
     *
     * @return string regexp corresponding to a given string or regexp
     */
    protected function toRegex(string $string): string
    {
        return $this->isRegex($string) ? $string : '/^' . \preg_quote($string, '/') . '/';
    }
}
