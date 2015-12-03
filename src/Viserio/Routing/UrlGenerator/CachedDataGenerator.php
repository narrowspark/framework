<?php
namespace Viserio\Routing\UrlGenerator;

use Viserio\Contracts\Routing\DataGenerator as DataGeneratorContract;
use Viserio\Filesystem\Filesystem;

/**
 * CachedDataGenerator.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
class CachedDataGenerator implements DataGeneratorContract
{
    /**
     * Filesystem.
     *
     * @var \Viserio\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @var \Viserio\Contracts\Routing\DataGenerator
     */
    protected $wrappedGenerator;

    /**
     * @var
     */
    protected $cacheFile;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * Constructor.
     *
     * @param \Viserio\Filesystem\Filesystem           $files
     * @param \Viserio\Contracts\Routing\DataGenerator $wrappedGenerator
     * @param string                                     $cacheFile
     * @param bool                                       $debug
     */
    public function __construct(
        Filesystem $files,
        DataGeneratorContract $wrappedGenerator,
        $cacheFile,
        $debug = false
    ) {
        $this->wrappedGenerator = $wrappedGenerator;
        $this->cacheFile = $cacheFile;
        $this->debug = $debug;

        $this->files = $files;
    }

    /**
     * Get formatted route data for use by a URL generator.
     *
     * @return array
     */
    public function getData()
    {
        $files = $this->files;
        $cache = $this->cacheFile;

        if (!$files->exists($cache) || !$this->debug) {
            $routes = $this->wrappedGenerator->getData();
            $files->put($cache, '<?php return '.var_export($routes, true).';');
        }

        return (array) $files->getRequire($this->cacheFile);
    }
}
