<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Handler;

use Cake\Chronos\Chronos;
use Symfony\Component\Finder\Finder;
use Viserio\Component\Contract\Filesystem\Filesystem as FilesystemContract;

class FileSessionHandler extends AbstractSessionHandler
{
    /**
     * The filesystem instance.
     *
     * @var \Viserio\Component\Contract\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The path where sessions should be stored.
     *
     * @var string
     */
    protected $path;

    /**
     * The number of seconds the session should be valid.
     *
     * @var int
     */
    protected $lifetime;

    /**
     * Create a new file driven handler instance.
     *
     * @param \Viserio\Component\Contract\Filesystem\Filesystem $files
     * @param string                                            $path
     * @param int                                               $lifetime The session lifetime in seconds
     */
    public function __construct(FilesystemContract $files, string $path, int $lifetime)
    {
        $this->path     = $path;
        $this->files    = $files;
        $this->lifetime = $lifetime;
    }

    /**
     * {@inheritdoc}
     */
    protected function doRead($sessionId): string
    {
        $path = $this->path . '/' . $sessionId;

        if ($this->files->has($path)) {
            $chronos = Chronos::now()->subSeconds($this->lifetime);

            if (\strtotime($this->files->getTimestamp($path)) >= $chronos->getTimestamp()) {
                return (string) $this->files->read($path);
            }
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite($sessionId, $sessionData): bool
    {
        return $this->files->write($this->path . '/' . $sessionId, $sessionData, ['lock' => true]);
    }

    /**
     * {@inheritdoc}
     */
    protected function doDestroy($sessionId): bool
    {
        return $this->files->delete([$this->path . '/' . $sessionId]);
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
        $files = Finder::create()
            ->in($this->path)
            ->files()
            ->ignoreDotFiles(false)
            ->date('<= now - ' . $maxlifetime . ' seconds');

        $boolArray = [];

        /** @var \SplFileObject $file */
        foreach ($files as $file) {
            $boolArray[] = $this->files->delete([$file->getRealPath()]);
        }

        return ! \in_array('false', $boolArray, true);
    }

    /**
     * {@inheritdoc}
     */
    public function updateTimestamp($sessionId, $data): bool
    {
        return \touch($this->path . '/' . $sessionId, Chronos::now() + $this->lifetime);
    }
}
