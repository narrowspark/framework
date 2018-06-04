<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Traits;

trait FilesystemExtensionTrait
{
    /**
     * Extract the file extension from a file path.
     *
     * @param string $path
     *
     * @return string
     */
    public function getExtension(string $path): string
    {
        return \pathinfo($this->getNormalizedOrPrefixedPath($path), \PATHINFO_EXTENSION);
    }

    /**
     * Returns the filename without the extension from a file path.
     *
     * @param string      $path      The path string
     * @param null|string $extension If specified, only that extension is cut off
     *                               (may contain leading dot)
     *
     * @return string Filename without extension
     */
    public function withoutExtension(string $path, string $extension = null): string
    {
        $path = $this->getNormalizedOrPrefixedPath($path);

        if ($extension !== null) {
            // remove extension and trailing dot
            return \rtrim(\basename($path, $extension), '.');
        }

        return \pathinfo($path, \PATHINFO_FILENAME);
    }

    /**
     * Changes the extension of a path string.
     *
     * @param string $path      The path string with filename.ext to change
     * @param string $extension New extension (with or without leading dot)
     *
     * @return string The path string with new file extension
     */
    public function changeExtension(string $path, string $extension): string
    {
        $path = $this->getNormalizedOrPrefixedPath($path);

        $explode    = \explode('.', $path);
        $substrPath = \mb_substr($path, -1);

        // No extension for paths
        if ($substrPath === '/' || \is_dir($path)) {
            return $path;
        }

        $actualExtension = null;
        $extension       = \ltrim($extension, '.');

        if (\count($explode) >= 2 && ! \is_dir($path)) {
            $actualExtension = \mb_strtolower($extension);
        }

        // No actual extension in path
        if ($actualExtension === null) {
            return $path . ($substrPath === '.' ? '' : '.') . $extension;
        }

        return \mb_substr($path, 0, -\mb_strlen($actualExtension)) . $extension;
    }

    /**
     * Get normalize or prefixed path.
     *
     * @param string $path
     *
     * @return string
     */
    abstract protected function getNormalizedOrPrefixedPath(string $path): string;
}
