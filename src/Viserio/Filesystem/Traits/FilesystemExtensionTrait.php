<?php
namespace Viserio\Filesystem\Traits;

use Viserio\Contracts\Filesystem\Exception\FileNotFoundException;

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
        $explode = explode('.', $path);

        if ($actualExtension = end($explode)) {
            $actualExtension = strtolower($extension);
        }

        $extension = ltrim($extension, '.');

        // No extension for paths
        if (substr($path, -1) === '/') {
            return $path;
        }

        // No actual extension in path
        if (empty($actualExtension)) {
            return $path . (substr($path, -1) === '.' ? '' : '.') . $extension;
        }

        return substr($path, 0, -strlen($actualExtension)) . $extension;
    }
}
