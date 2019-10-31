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

namespace Viserio\Component\Session\Handler;

use Cake\Chronos\Chronos;

class FileSessionHandler extends AbstractSessionHandler
{
    /**
     * Get the file extension.
     *
     * @var string
     */
    public const FILE_EXTENSION = 'sess';

    /**
     * The path where sessions should be stored.
     *
     * @var string
     */
    private $path;

    /**
     * The number of seconds the session should be valid.
     *
     * @var int
     */
    private $lifetime;

    /**
     * Create a new file driven handler instance.
     *
     * @param string $path
     * @param int    $lifetime The session lifetime in seconds
     */
    public function __construct(string $path, int $lifetime)
    {
        $this->path = $path;
        $this->lifetime = $lifetime;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Cleanup old sessions.
     *
     * @see https://php.net/manual/en/sessionhandlerinterface.gc.php
     *
     * @param int $maxlifetime
     *
     * @return bool
     */
    public function gc($maxlifetime): bool
    {
        $files = \array_filter(
            \glob($this->path . \DIRECTORY_SEPARATOR . '*.' . self::FILE_EXTENSION, \GLOB_NOSORT | \GLOB_BRACE),
            'is_file'
        );
        $boolArray = [];

        foreach ($files as $filePath) {
            $filePath = $filePath;

            if (\file_exists($filePath) && (int) \filemtime($filePath) + $maxlifetime < \time()) {
                $boolArray[] = @\unlink($filePath);
            }
        }

        return ! \in_array(false, $boolArray, true);
    }

    /**
     * Update timestamp of a session.
     *
     * @param string $sessionId   The session id
     * @param string $sessionData
     *
     * @return bool
     */
    public function updateTimestamp($sessionId, $sessionData): bool
    {
        // touch wont work on windows.
        return \touch(
            $this->path . \DIRECTORY_SEPARATOR . $sessionId . '.' . self::FILE_EXTENSION,
            Chronos::now()->addSeconds($this->lifetime)->getTimestamp()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function doRead($sessionId): string
    {
        $filePath = $this->path . \DIRECTORY_SEPARATOR . $sessionId . '.' . self::FILE_EXTENSION;

        if (\file_exists($filePath)) {
            $timestamp = Chronos::now()->subSeconds($this->lifetime)->getTimestamp();

            if (\filemtime($filePath) >= $timestamp) {
                return (string) \file_get_contents($filePath);
            }
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite($sessionId, $sessionData): bool
    {
        return \is_int(\file_put_contents(
            $this->path . \DIRECTORY_SEPARATOR . $sessionId . '.' . self::FILE_EXTENSION,
            $sessionData,
            \LOCK_EX
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function doDestroy($sessionId): bool
    {
        return @\unlink($this->path . \DIRECTORY_SEPARATOR . $sessionId . '.' . self::FILE_EXTENSION);
    }
}
