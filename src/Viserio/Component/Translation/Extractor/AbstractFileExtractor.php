<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Extractor;

use SplFileInfo;
use Traversable;
use Viserio\Component\Contract\Translation\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Translation\Extractor as ExtractorContract;

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
     * @param array|string $resource Files, a file or a directory
     *
     * @return array
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
     * @param string $file
     *
     * @throws \Viserio\Component\Contract\Translation\Exception\InvalidArgumentException
     *
     * @return bool
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
     *
     * @param string $file
     *
     * @return bool
     */
    abstract protected function canBeExtracted(string $file): bool;

    /**
     * @param array|string $resource Files, a file or a directory
     *
     * @return array files to be extracted
     */
    abstract protected function extractFromDirectory($resource): array;
}
