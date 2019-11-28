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

namespace Viserio\Component\Filesystem\Tests;

class TestStreamWrapper
{
    /** @var string[] */
    private static $basePaths = [];

    /** @var resource */
    private $handle;

    public static function register($scheme, $basePath): void
    {
        self::$basePaths[$scheme] = $basePath;

        \stream_wrapper_register($scheme, __CLASS__);
    }

    public static function unregister($scheme): void
    {
        if (! isset(self::$basePaths[$scheme])) {
            return;
        }

        unset(self::$basePaths[$scheme]);

        \stream_wrapper_unregister($scheme);
    }

    public function dir_opendir($uri, $options): bool
    {
        $this->handle = \opendir($this->uriToPath($uri));

        return true;
    }

    public function dir_closedir(): bool
    {
        \assert(null !== $this->handle);

        \closedir($this->handle);

        return false;
    }

    public function dir_readdir()
    {
        \assert(null !== $this->handle);

        return \readdir($this->handle);
    }

    public function dir_rewinddir(): bool
    {
        \assert(null !== $this->handle);

        \rewinddir($this->handle);

        return true;
    }

    public function mkdir($uri, $mode, $options): void
    {
    }

    public function rename($uriFrom, $uriTo): void
    {
    }

    public function rmdir($uri, $options): void
    {
    }

    public function stream_cast($castAs)
    {
        return $this->handle;
    }

    public function stream_close(): void
    {
    }

    public function stream_eof(): void
    {
    }

    public function stream_flush(): void
    {
    }

    public function stream_lock($operation): void
    {
    }

    public function stream_metadata($uri, $option, $value): void
    {
    }

    public function stream_open($uri, $mode, $options, &$openedPath): void
    {
    }

    public function stream_read($length): void
    {
    }

    public function stream_seek($offset, $whence = \SEEK_SET): void
    {
    }

    public function stream_set_option($option, $arg1, $arg2): void
    {
    }

    public function stream_stat(): void
    {
    }

    public function stream_tell(): void
    {
    }

    public function stream_truncate($newSize): void
    {
    }

    public function stream_write($data): void
    {
    }

    public function unlink($uri): void
    {
    }

    public function url_stat($uri, $flags)
    {
        $path = $this->uriToPath($uri);

        if ($flags & \STREAM_URL_STAT_LINK) {
            return \lstat($path);
        }

        return @\stat($path);
    }

    private function uriToPath($uri): string
    {
        $parts = \explode('://', $uri);

        return self::$basePaths[$parts[0]] . $parts[1];
    }
}
