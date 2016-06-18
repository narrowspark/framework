<?php
namespace Viserio\Session\Handler;

use Carbon\Carbon;
use SessionHandlerInterface;
use Symfony\Component\Finder\Finder;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;

class FileSessionHandler implements SessionHandlerInterface
{
    /**
     * The filesystem instance.
     *
     * @var FilesystemContract
     */
    protected $files;

    /**
     * The path where sessions should be stored.
     *
     * @var string
     */
    protected $path;

    /**
     * The number of minutes the session should be valid.
     *
     * @var int
     */
    protected $lifetime;

    /**
     * Create a new file driven handler instance.
     *
     * @param FilesystemContract $files
     * @param string             $path
     * @param int                $lifetime The session lifetime in minutes
     */
    public function __construct(FilesystemContract $files, string $path, int $lifetime)
    {
        $this->path = $path;
        $this->files = $files;
        $this->lifetime = $lifetime;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $name)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        $path = $this->path . '/' . $sessionId;

        if ($this->files->has($path)) {
            if (strtotime($this->files->getTimestamp($path)) >= Carbon::now()->subMinutes($this->lifetime)->getTimestamp()) {
                return $this->files->read($path);
            }
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $sessionData)
    {
        return $this->files->write($this->path . '/' . $sessionId, $sessionData, ['lock' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        return $this->files->delete([$this->path . '/' . $sessionId]);
    }

    /**
     * {@inheritdoc}
     */
    public function gc($maxlifetime)
    {
        $files = Finder::create()
            ->in($this->path)
            ->files()
            ->ignoreDotFiles(false)
            ->date('<= now - ' . $maxlifetime . ' seconds');

        $boolArray = [];

        foreach ($files as $file) {
            $boolArray[] = $this->files->delete([$file->getRealPath()]);
        }

        return !in_array('false', $boolArray, true);
    }
}
