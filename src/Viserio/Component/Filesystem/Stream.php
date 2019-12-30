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

namespace Viserio\Component\Filesystem;

use Viserio\Contract\Filesystem\Exception\RuntimeException;

/**
 * @internal
 */
final class Stream
{
    /**
     * Name of stream protocol - narrowspark.safe://.
     */
    public const PROTOCOL = 'narrowspark.safe';

    /**
     * Switch whether class has already been registered as stream wrapper or not.
     *
     * @var bool
     */
    private static $registered = false;

    /**
     * Original file handle.
     *
     * @var null|false|resource
     */
    private $handle;

    /**
     * Temporary file handle.
     *
     * @var null|false|resource
     */
    private $tempHandle;

    /**
     * Original file path.
     *
     * @var null|string
     */
    private $file;

    /**
     * Temporary file path.
     *
     * @var null|string
     */
    private $tempFile;

    /**
     * Should file be deleted.
     *
     * @var bool
     */
    private $deleteFile = false;

    /**
     * Was a error detected.
     *
     * @var bool
     */
    private $writeError = false;

    /**
     * Registers protocol 'narrowspark.safe://'.
     *
     * Is already another stream wrapper registered for the scheme,
     * a RuntimeException will be thrown.
     *
     * @return bool
     */
    public static function register(): bool
    {
        if (self::$registered === true) {
            return true;
        }

        if (@\stream_wrapper_register(self::PROTOCOL, self::class) === false) {
            throw new RuntimeException(\sprintf('A handler has already been registered for the [%s] protocol.', self::PROTOCOL));
        }

        return self::$registered = true;
    }

    /**
     * Unregisters a previously registered URL wrapper for the safe and narrowspark.safe scheme.
     *
     * If this stream wrapper wasn't registered, the method returns silently.
     *
     * If unregistering fails, or if the URL wrapper for narrowspark.safe// was not
     * registered with this class, a RuntimeException will be thrown.
     *
     * @return void
     */
    public static function unregister(): void
    {
        if (! self::$registered) {
            if (\in_array(self::PROTOCOL, \stream_get_wrappers(), true)) {
                throw new RuntimeException('The URL wrapper for the protocol ' . self::PROTOCOL . ' was not registered.');
            }

            return;
        }

        if (! @\stream_wrapper_unregister(self::PROTOCOL)) {
            throw new RuntimeException('Failed to unregister the URL wrapper for the ' . self::PROTOCOL . ' protocol.');
        }

        self::$registered = false;
    }

    /**
     * Opens file.
     *
     * @param string      $path        the path to open
     * @param string      $mode        mode for opening
     * @param int         $options     options for opening
     * @param null|string $opened_path full path that was actually opened
     *
     * @return bool
     */
    public function stream_open(string $path, string $mode, int $options, ?string $opened_path = null): bool
    {
        $path = \substr($path, (int) \strpos($path, ':') + 3);  // trim protocol narrowspark.safe://
        $flag = \trim($mode, 'crwax+');  // text | binary mode
        $mode = \trim($mode, 'tb');     // mode
        $usePath = (bool) (\STREAM_USE_PATH & $options); // use include_path?

        if ($mode === 'r') { // provides only isolation
            return $this->checkAndLock($this->tempHandle = \fopen($path, 'r' . $flag, $usePath), \LOCK_SH);
        }

        if ($mode === 'r+') {
            if (! $this->checkAndLock($this->handle = \fopen($path, 'r' . $flag, $usePath), \LOCK_EX)) {
                return false;
            }
        } elseif (\strpos($mode, 'x') === 0) {
            if (! $this->checkAndLock($this->handle = \fopen($path, 'x' . $flag, $usePath), \LOCK_EX)) {
                return false;
            }

            $this->deleteFile = true;
        } elseif (\strpos($mode, 'w') === 0 || \strpos($mode, 'a') === 0 || \strpos($mode, 'c') === 0) {
            if ($this->checkAndLock($this->handle = @\fopen($path, 'x' . $flag, $usePath), \LOCK_EX)) { // intentionally @
                $this->deleteFile = true;
            } elseif (! $this->checkAndLock($this->handle = \fopen($path, 'a+' . $flag, $usePath), \LOCK_EX)) {
                return false;
            }
        } else {
            if (($options & \STREAM_REPORT_ERRORS) === \STREAM_REPORT_ERRORS) {
                trigger_error(
                    \sprintf('Illegal mode [%s], use r, w, a, x  or c, flavoured with t, b and/or +', $mode),
                    \E_USER_WARNING
                );
            }

            return false;
        }

        // create temporary file in the same directory to provide atomicity
        $tmp = '~~' . \lcg_value() . '.tmp';

        if (($this->tempHandle = \fopen($path . $tmp, (\strpos($mode, '+') !== false ? 'x+' : 'x') . $flag, $usePath)) === false) {
            $this->clean();

            return false;
        }

        $this->tempFile = $this->resolvePath($path . $tmp);
        $this->file = \substr($this->tempFile, 0, -\strlen($tmp));

        // copy to temporary file
        if (\is_resource($this->handle) && ($mode === 'r+' || \strpos($mode, 'a') === 0 || \strpos($mode, 'c') === 0)) {
            $stat = \fstat($this->handle);

            \fseek($this->handle, 0);

            if (\stream_copy_to_stream($this->handle, $this->tempHandle) !== $stat[7] /* size */) {
                $this->clean();

                return false;
            }

            if (\strpos($mode, 'a') === 0) { // emulate append mode
                \fseek($this->tempHandle, 0, \SEEK_END);
            }
        }

        return true;
    }

    /**
     * Checks handle and locks file.
     *
     * @param false|resource $handle
     * @param int            $lock
     *
     * @return bool
     */
    private function checkAndLock($handle, int $lock): bool
    {
        if ($handle === false) {
            return false;
        }

        if (! \flock($handle, $lock)) {
            \fclose($handle);

            return false;
        }

        return true;
    }

    /**
     * Error destructor.
     *
     * @return void
     */
    private function clean(): void
    {
        if (! \is_resource($this->handle)) {
            return;
        }

        \flock($this->handle, \LOCK_UN);
        \fclose($this->handle);

        if ($this->deleteFile && $this->file !== null) {
            \unlink($this->file);
        }

        if (\is_resource($this->tempHandle) && $this->tempFile !== null) {
            \fclose($this->tempHandle);
            \unlink($this->tempFile);
        }
    }

    /**
     * Close a resource.
     *
     * @return void
     */
    public function stream_close(): void
    {
        if ($this->tempFile === null && \is_resource($this->tempHandle)) { // 'r' mode
            \flock($this->tempHandle, \LOCK_UN);
            \fclose($this->tempHandle);

            return;
        }

        if (\is_resource($this->handle) && \is_resource($this->tempHandle)) {
            \flock($this->handle, \LOCK_UN);
            \fclose($this->handle);
            \fclose($this->tempHandle);

            if (($this->writeError && $this->tempFile !== null) || ($this->tempFile !== null && $this->file !== null && ! \rename($this->tempFile, $this->file))) { // try to rename temp file
                \unlink($this->tempFile); // otherwise delete temp file

                if ($this->deleteFile && $this->file !== null) {
                    \unlink($this->file);
                }
            }
        }
    }

    /**
     * Reads up to length bytes from the file.
     *
     * @param int $length
     *
     * @return bool|string
     */
    public function stream_read(int $length)
    {
        if ($this->tempHandle === null || \get_resource_type($this->tempHandle) !== 'stream') {
            trigger_error(\sprintf('The [$tempHandle] property of [%s] need to be a stream.', __CLASS__), \E_USER_WARNING);

            return false;
        }

        /** @var resource $resource */
        $resource = $this->tempHandle;

        return \fread($resource, $length);
    }

    /**
     * Writes the string to the file.
     *
     * @param string $data
     *
     * @return int
     */
    public function stream_write(string $data): int
    {
        if (! \is_resource($this->tempHandle)) {
            return 0;
        }

        $len = \strlen($data);
        $res = (int) \fwrite($this->tempHandle, $data, $len);

        if ($res !== $len) { // disk full?
            $this->writeError = true;
        }

        return $res;
    }

    /**
     * Truncates a file to a given length.
     *
     * @param int $size
     *
     * @return bool
     */
    public function stream_truncate(int $size): bool
    {
        if ($this->tempHandle === null || \get_resource_type($this->tempHandle) !== 'stream') {
            trigger_error(\sprintf('The [$tempHandle] property of [%s] need to be a stream.', __CLASS__), \E_USER_WARNING);

            return false;
        }

        /** @var resource $resource */
        $resource = $this->tempHandle;

        return \ftruncate($resource, $size);
    }

    /**
     * Returns the position of the file.
     *
     * @return false|int
     */
    public function stream_tell()
    {
        if ($this->tempHandle === null || \get_resource_type($this->tempHandle) !== 'stream') {
            trigger_error(\sprintf('The [$tempHandle] property of [%s] need to be a stream.', __CLASS__), \E_USER_WARNING);

            return false;
        }

        /** @var resource $resource */
        $resource = $this->tempHandle;

        return \ftell($resource);
    }

    /**
     * Returns true if the file pointer is at end-of-file.
     *
     * @return bool
     */
    public function stream_eof(): bool
    {
        if ($this->tempHandle === null || \get_resource_type($this->tempHandle) !== 'stream') {
            trigger_error(\sprintf('The [$tempHandle] property of [%s] need to be a stream.', __CLASS__), \E_USER_WARNING);

            return false;
        }

        /** @var resource $resource */
        $resource = $this->tempHandle;

        return \feof($resource);
    }

    /**
     * Sets the file position indicator for the file.
     *
     * @param int $offset
     * @param int $whence
     *
     * @return bool
     */
    public function stream_seek(int $offset, int $whence = \SEEK_SET): bool
    {
        if ($this->tempHandle === null || \get_resource_type($this->tempHandle) !== 'stream') {
            trigger_error(\sprintf('The [$tempHandle] property of [%s] need to be a stream.', __CLASS__), \E_USER_WARNING);

            return false;
        }

        /** @var resource $resource */
        $resource = $this->tempHandle;

        return \fseek($resource, $offset, $whence) === 0;
    }

    /**
     * Gets information about a file referenced by $this->tempHandle.
     *
     * @return array<int|string, false|int>
     */
    public function stream_stat(): array
    {
        if ($this->tempHandle === null || \get_resource_type($this->tempHandle) !== 'stream') {
            trigger_error(\sprintf('The [$tempHandle] property of [%s] need to be a stream.', __CLASS__), \E_USER_WARNING);

            return [];
        }

        /** @var resource $resource */
        $resource = $this->tempHandle;

        return (array) \fstat($resource);
    }

    /**
     * Change stream options.
     *
     * @param string $path
     * @param int    $option STREAM_META_TOUCH, STREAM_META_OWNER_NAME, STREAM_META_OWNER, STREAM_META_GROUP_NAME, STREAM_META_GROUP, STREAM_META_ACCESS
     * @param mixed  $args   variable arguments
     *
     * @return bool Returns TRUE on success or FALSE on failure
     */
    public function stream_metadata(string $path, int $option, $args): bool
    {
        $path = \substr($path, (int) \strpos($path, ':') + 3);

        switch ($option) {
            case \STREAM_META_TOUCH:
                $time = $args[0] ?? null;
                $atime = $args[1] ?? null;

                if ($atime !== null) {
                    /** @noinspection PotentialMalwareInspection */
                    return \touch($path, $time, $atime);
                }

                if ($time !== null) {
                    return \touch($path, $time);
                }

                return \touch($path);
            case \STREAM_META_OWNER_NAME:
            case \STREAM_META_OWNER:
                return \chown($path, $args);
            case \STREAM_META_GROUP_NAME:
            case \STREAM_META_GROUP:
                return \chgrp($path, $args);
            case \STREAM_META_ACCESS:
                return \chmod($path, $args);

            default:
                return false;
        }
    }

    /**
     * Gets information about a file referenced by filename.
     *
     * @param string $path
     * @param int    $flags
     *
     * @return array<int|string, false|int>|false
     */
    public function url_stat(string $path, int $flags)
    {
        // This is not thread safe
        $path = \substr($path, (int) \strpos($path, ':') + 3);

        return ($flags & \STREAM_URL_STAT_LINK) === 0 ? @\lstat($path) : @\stat($path); // intentionally @
    }

    /**
     * Deletes a file.
     * On Windows unlink is not allowed till file is opened.
     *
     * @param string $path
     *
     * @return bool
     */
    public function unlink(string $path): bool
    {
        return \unlink(\substr($path, (int) \strpos($path, ':') + 3));
    }

    /**
     * Change stream options.
     *
     * @param int      $option
     * @param int      $arg1
     * @param null|int $arg2
     *
     * @return bool
     */
    public function stream_set_option(int $option, int $arg1, ?int $arg2): bool
    {
        if ($this->tempHandle === null || \get_resource_type($this->tempHandle) !== 'stream') {
            \trigger_error(\sprintf('The [$tempHandle] property of [%s] need to be a stream.', __CLASS__), \E_USER_WARNING);

            return false;
        }

        /** @var resource $resource */
        $resource = $this->tempHandle;

        switch ($option) {
            case \STREAM_OPTION_BLOCKING:
                return $this->stream_set_blocking($resource, (bool) $arg1);
            case \STREAM_OPTION_READ_TIMEOUT:
                return $this->stream_set_timeout($resource, $arg1, $arg2 ?? 0);
            case \STREAM_OPTION_WRITE_BUFFER:
                return (bool) $this->stream_set_write_buffer($resource, $arg1);

            default:
                \trigger_error(\sprintf('The option [%s] is unknown for [stream_set_option] method', $option), \E_USER_WARNING);

                return false;
        }
    }

    /**
     * Set blocking/non-blocking mode on a stream.
     *
     * @param resource $stream
     * @param bool     $mode
     *
     * @return bool
     */
    public function stream_set_blocking($stream, bool $mode): bool
    {
        if ($mode === true) {
            \trigger_error(\sprintf('Blocking mode is not supported yet with [%s] class.', __CLASS__), \E_USER_WARNING);

            return false;
        }

        return true;
    }

    /**
     * Sets write file buffering on the given stream.
     *
     * @param resource $stream
     * @param int      $buffer
     *
     * @return int
     */
    public function stream_set_write_buffer($stream, int $buffer): int
    {
        \trigger_error(\sprintf('Writting with [%s] class, is not supported yet.', __CLASS__), \E_USER_WARNING);

        return 0;
    }

    /**
     * Set timeout period on a stream.
     *
     * @param resource $stream
     * @param int      $seconds
     * @param int      $microseconds
     *
     * @return bool
     */
    public function stream_set_timeout($stream, int $seconds, int $microseconds = 0): bool
    {
        \trigger_error(\sprintf('Set timeout with [%s] class, is not supported yet.', __CLASS__), \E_USER_WARNING);

        return false;
    }

    /**
     * Helper method to resolve a path from /foo/bar/. to /foo/bar.
     *
     * @param string $path
     *
     * @return string
     */
    private function resolvePath(string $path): string
    {
        $newPath = [];

        foreach (\explode('/', $path) as $pathPart) {
            if ($pathPart === '.') {
                continue;
            }

            if ($pathPart !== '..') {
                $newPath[] = $pathPart;
            } elseif (\count($newPath) > 1) {
                \array_pop($newPath);
            }
        }

        return \implode('/', $newPath);
    }
}
