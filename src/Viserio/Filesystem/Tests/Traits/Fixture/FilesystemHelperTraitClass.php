<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Tests\Traits\Fixture;

use Viserio\Filesystem\Traits\FilesystemHelperTrait;
use Viserio\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class FilesystemHelperTraitClass
{
    use FilesystemHelperTrait;
    use NormalizePathAndDirectorySeparatorTrait;

    public function has(string $path)
    {
        return file_exists($path);
    }

    public function isDirectory(string $dirname)
    {
        return is_dir($dirname);
    }

    /**
     * Get normalize or prefixed path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getNormalzedOrPrefixedPath(string $path): string
    {
        if (isset($this->driver)) {
            $prefix = method_exists($this->driver, 'getPathPrefix') ? $this->driver->getPathPrefix() : '';

            return $prefix . $path;
        }

        return self::normalizeDirectorySeparator($path);
    }
}
