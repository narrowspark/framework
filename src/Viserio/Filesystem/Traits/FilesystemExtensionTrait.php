<?php
namespace Viserio\Filesystem\Traits;

trait FilesystemExtensionTrait
{
    /**
     * {@inheritdoc}
     */
    public function getExtension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * {@inheritdoc}
     */
    public function withoutExtension($path, $extension = null)
    {
        if ($extension !== null) {
            // remove extension and trailing dot
            return rtrim(basename($path, $extension), '.');
        }

        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * {@inheritdoc}
     */
    public function changeExtension($path, $extension)
    {
        $explode    = explode('.', $path);
        $substrPath = substr($path, -1);

        // No extension for paths
        if ($substrPath === '/' || is_dir($path)) {
            return $path;
        }

        $actualExtension = null;
        $extension       = ltrim($extension, '.');

        if (count($explode) >= 2 && !is_dir($path)) {
            $actualExtension = strtolower($extension);
        }

        // No actual extension in path
        if ($actualExtension === null) {
            return $path . ($substrPath === '.' ? '' : '.') . $extension;
        }

        return substr($path, 0, -strlen($actualExtension)) . $extension;
    }
}
