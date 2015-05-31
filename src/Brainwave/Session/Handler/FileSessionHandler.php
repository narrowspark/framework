<?php

namespace Brainwave\Session\Handler;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Brainwave\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Session.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
class FileSessionHandler implements \SessionHandlerInterface
{
    /**
     * The current session ID that's open.
     *
     * @var string
     */
    private $currentId;

    /**
     * The filesystem instance.
     *
     * @var \Brainwave\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The session file pointer.
     *
     * @var resource
     */
    protected $fp;

    /**
     * The path where sessions should be stored.
     *
     * @var string
     */
    protected $path;

    /**
     * Create a new file driven handler instance.
     *
     * @param \Brainwave\Filesystem\Filesystem $files
     * @param string                           $path
     */
    public function __construct(Filesystem $files, $path)
    {
        $this->path = $path;
        $this->files = $files;
    }

    /**
     * {@inheritDoc}
     */
    public function open($savePath, $sessionName)
    {
        // close any open files before opening something new
        $this->close();

        $path = $this->path.'/'.$sessionName;

        $this->currentId = $sessionName;
        $this->fp = fopen($path, 'c+b');

        // Obtain a write lock - must explicitly perform this because
        // the underlying OS may be advisory as opposed to mandatory
        $locked = flock($this->fp, LOCK_EX);
        if (!$locked) {
            fclose($this->fp);
            $this->fp = null;
            $this->currentId = null;

            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        // only close if there is something to close
        if ($this->fp) {
            flock($this->fp, LOCK_UN);
            fclose($this->fp);
            $this->fp = null;
            $this->currentId = null;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function read($sessionId)
    {
        // if the proper session file isn't open, open it
        if ($sessionId !== $this->currentId || !$this->fp) {
            if (!$this->open($this->path, $sessionId)) {
                throw new \Exception('Could not open session file');
            }
        } else {
            // otherwise make sure we are at the beginning of the file
            rewind($this->fp);
        }

        $data = '';
        while (!feof($this->fp)) {
            $data .= fread($this->fp, 8192);
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function write($sessionId, $data)
    {
        if ($sessionId !== $this->currentId || !$this->fp) {
            if (!$this->open($this->path, $sessionId)) {
                throw new \Exception('Could not open session file');
            }
        }

        ftruncate($this->fp, 0);
        rewind($this->fp);
        fwrite($this->fp, $data);

        $this->close();
    }

    /**
     * {@inheritDoc}
     */
    public function destroy($sessionId)
    {
        $this->files->delete($this->path.'/'.$sessionId);
    }

    /**
     * {@inheritDoc}
     */
    public function gc($lifetime)
    {
        // a race condition exists such that garbage collection will throw a
        // runtime exception if a file in the iterator object returned by the
        // Finder call in the parent function is deleted out of band before the
        // iterator call (foreach) gets to it.  this just catches those
        // exceptions and retries the call (currently set arbitrarily at
        // 5 retries
        $retries = 5;

        for ($i = 0; $i < $retries; $i++) {
            try {
                $files = Finder::create()
                    ->in($this->path)
                    ->files()
                    ->ignoreDotFiles(true)
                    ->date('<= now - '.$lifetime.' seconds');

                foreach ($files as $file) {
                    $this->files->delete($file->getRealPath());
                }
            } catch (\RuntimeException $exception) {
                continue;
            }

            break;
        }
    }
}
