<?php
namespace Viserio\Contracts\Filesystem;

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
