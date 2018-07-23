<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Handler;

use Cake\Chronos\Chronos;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class FileSessionHandler extends AbstractSessionHandler
{
    use NormalizePathAndDirectorySeparatorTrait;

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
        $this->path     = self::normalizeDirectorySeparator($path);
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
     * {@inheritdoc}
     */
    public function gc($maxlifetime): bool
    {
        $files = \array_filter(
            \glob($this->path . '/*.' . self::FILE_EXTENSION, \GLOB_BRACE),
            'is_file'
        );
        $boolArray = [];

        foreach ($files as $filePath) {
            $filePath = self::normalizeDirectorySeparator($filePath);

            if (\file_exists($filePath) && (int) \filemtime($filePath) + $maxlifetime < \time()) {
                $boolArray[] = @\unlink($filePath);
            }
        }

        return ! \in_array(false, $boolArray, true);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($sessionId, $data): bool
    {
        // touch wont work on windows.
        return \touch(
            self::normalizeDirectorySeparator($this->path . '/' . $sessionId . '.' . self::FILE_EXTENSION),
            Chronos::now()->addSeconds($this->lifetime)->getTimestamp()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function doRead($sessionId): string
    {
        $filePath = self::normalizeDirectorySeparator($this->path . '/' . $sessionId . '.' . self::FILE_EXTENSION);

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
            self::normalizeDirectorySeparator($this->path . '/' . $sessionId . '.' . self::FILE_EXTENSION),
            $sessionData,
            \LOCK_EX
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function doDestroy($sessionId): bool
    {
        return @\unlink(
            self::normalizeDirectorySeparator($this->path . '/' . $sessionId . '.' . self::FILE_EXTENSION)
        );
    }
}
