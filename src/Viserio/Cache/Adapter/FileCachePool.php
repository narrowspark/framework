<?php
namespace Viserio\Cache\Adapter;

use Exception;
use Cache\Adapter\Common\AbstractCachePool;
use Narrowspark\Arr\StaticArr as Arr;
use Psr\Cache\CacheItemInterface;
use Viserio\Filesystem\Filesystem;

class FileCachePool extends AbstractCachePool
{
    /**
     * The Viserio Filesystem instance.
     *
     * @var \Viserio\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The file cache directory.
     *
     * @var string
     */
    protected $directory;

    /**
     * Create a new file cache store instance.
     *
     * @param \Viserio\Filesystem\Filesystem $files
     * @param string                         $directory
     */
    public function __construct(Filesystem $files, $directory)
    {
        $this->files     = $files;
        $this->directory = $directory;
    }

    protected function fetchObjectFromCache($key)
    {
        return Arr::get($this->getPayload($key), 'data', null);
    }

    protected function clearAllObjectsFromCache()
    {
        if ($this->files->isDirectory($this->directory)) {
            foreach ($this->files->directories($this->directory) as $directory) {
                $this->files->deleteDirectory($directory);
            }
        }
    }

    protected function clearOneObjectFromCache($key)
    {
        $file = $this->path($key);

        if ($this->files->exists($file)) {
            return $this->files->delete($file);
        }

    }

    protected function storeItemInCache($key, CacheItemInterface $item, $ttl)
    {
        $item = $this->expiration($minutes) . serialize($item);

        $this->createCacheDirectory($path = $this->path($key));

        $this->files->put($path, $item);
    }

    /**
     * Retrieve an item and expiry time from the cache by key.
     *
     * @param string $key
     *
     * @return array
     */
    protected function getPayload($key)
    {
        $path = $this->path($key);

        // If the file doesn't exists, we obviously can't return the cache so we will
        // just return null. Otherwise, we'll get the contents of the file and get
        // the expiration UNIX timestamps from the start of the file's contents.
        try {
            $expire = substr($contents = $this->files->get($path), 0, 10);
        } catch (Exception $exception) {
            return ['data' => null, 'time' => null];
        }

        // If the current time is greater than expiration timestamps we will delete
        // the file and return null. This helps clean up the old files and keeps
        // this directory much cleaner for us as old files aren't hanging out.
        if (time() >= $expire) {
            $this->forget($key);

            return ['data' => null, 'time' => null];
        }

        $data = unserialize(substr($contents, 10));

        // Next, we'll extract the number of minutes that are remaining for a cache
        // so that we can properly retain the time for things like the increment
        // operation that may be performed on the cache. We'll round this out.
        $time = ceil(($expire - time()) / 60);

        return compact('data', 'time');
    }

    /**
     * Create the file cache directory if necessary.
     *
     * @param string $path
     */
    protected function createCacheDirectory($path)
    {
        try {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        } catch (Exception $exception) {
            //
        }
    }

    /**
     * Get the full path for the given cache key.
     *
     * @param string $key
     *
     * @return string
     */
    protected function path($key)
    {
        $parts = array_slice(str_split($hash = md5($key), 2), 0, 2);

        return $this->directory . '/' . implode('/', $parts) . '/' . $hash;
    }

    /**
     * Get the expiration time based on the given minutes.
     *
     * @param int $minutes
     *
     * @return int
     */
    protected function expiration($minutes)
    {
        if ($minutes === 0) {
            return 9999999999;
        }

        return time() + ($minutes * 60);
    }
}
