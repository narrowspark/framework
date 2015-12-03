<?php
namespace Viserio\Contracts\Filesystem;

/**
 * FilesystemManager.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
interface FilesystemManager
{
    /**
     * Get a filesystem implementation.
     *
     * @param string|null $name
     *
     * @return \Viserio\Contracts\Filesystem\Filesystem
     */
    public function disk($name = null);
}
