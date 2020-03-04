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

namespace Viserio\Component\Translation\Extractor;

use SplFileInfo;
use Traversable;
use Viserio\Contract\Translation\Exception\InvalidArgumentException;
use Viserio\Contract\Translation\Extractor as ExtractorContract;

abstract class AbstractFileExtractor implements ExtractorContract
{
    /**
     * Prefix for new found message.
     *
     * @var string
     */
    protected $prefix = '';

    /**
     * {@inheritdoc}
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * Extract files from given resources.
     *
     * @param iterable|SplFileInfo|string $resource Files, a file or a directory
     */
    protected function extractFiles($resource): array
    {
        if (\is_array($resource) || $resource instanceof Traversable) {
            $files = [];

            foreach ($resource as $file) {
                if ($file instanceof SplFileInfo) {
                    $file = $file->getPathname();
                }

                if ($this->canBeExtracted($file)) {
                    $files[] = $file;
                }
            }
        } else {
            if ($resource instanceof SplFileInfo) {
                $resource = $resource->getPathname();
            }

            if (\is_file($resource)) {
                $files = $this->canBeExtracted($resource) ? [$resource] : [];
            } else {
                $files = $this->extractFromDirectory($resource);
            }
        }

        return $files;
    }

    /**
     * Check if is file.
     *
     * @throws \Viserio\Contract\Translation\Exception\InvalidArgumentException
     */
    protected function isFile(string $file): bool
    {
        if (! \is_file($file)) {
            throw new InvalidArgumentException(\sprintf('The [%s] file does not exist.', $file));
        }

        return true;
    }

    /**
     * Check if file can be extracted.
     */
    abstract protected function canBeExtracted(string $file): bool;

    /**
     * @param array|string $resource Files, a file or a directory
     *
     * @return array files to be extracted
     */
    abstract protected function extractFromDirectory($resource): array;
}
